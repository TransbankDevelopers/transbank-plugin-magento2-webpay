<?php
namespace Transbank\Webpay\Controller\Transaction;

use Transbank\Webpay\Model\TransbankSdkWebpay;
use Transbank\Webpay\Model\LogHandler;

use \Magento\Sales\Model\Order;


/**
 * Controller for commit transaction Webpay
 */
class CommitWebpayM22 extends \Magento\Framework\App\Action\Action {

    private $paymentTypeCodearray = array(
        "VD" => "Venta Debito",
        "VN" => "Venta Normal",
        "VC" => "Venta en cuotas",
        "SI" => "3 cuotas sin interés",
        "S2" => "2 cuotas sin interés",
        "NC" => "N cuotas sin interés",
    );

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Checkout\Model\Cart $cart,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
                                \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
                                \Transbank\Webpay\Model\Config\ConfigProvider $configProvider) {

        parent::__construct($context);

        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->messageManager = $context->getMessageManager();
        $this->configProvider = $configProvider;
        $this->log = new LogHandler();
    }

    /**
     * @Override
     */
    public function execute() {

        $config = $this->configProvider->getPluginConfig();
        $orderStatusCanceled = $config['error_pay'];

        try {

            $order = $this->getOrder();

            $tokenWs = isset($_POST['token_ws']) ? $_POST['token_ws'] : null;

            if($tokenWs != $this->checkoutSession->getTokenWs()) {
                throw new \Exception('Token inválido');
            }

            $paymentOk = $this->checkoutSession->getPaymentOk();

            if ($paymentOk  == 'WAITING') {

                $transbankSdkWebpay = new TransbankSdkWebpay($config);
                $result = $transbankSdkWebpay->commitTransaction($tokenWs);

                $this->checkoutSession->setResultWebpay($result);

                if (isset($result->buyOrder) && isset($result->detailOutput) && $result->detailOutput->responseCode == 0) {

                    $this->checkoutSession->setPaymentOk('SUCCESS');

                    $authorizationCode = $result->detailOutput->authorizationCode;
                    $payment = $order->getPayment();
                    $payment->setLastTransId($authorizationCode);
                    $payment->setTransactionId($authorizationCode);
                    $payment->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$result]);

                    $orderStatus = $config['sucefully_pay'];
                    $order->setState($orderStatus)->setStatus($orderStatus);
                    $order->addStatusToHistory($order->getStatus(), json_encode($result));
                    $order->save();

                    $this->checkoutSession->getQuote()->setIsActive(false)->save();

                    return $this->toRedirect($result->urlRedirection, array('token_ws' => $tokenWs));

                } else {

                    $this->checkoutSession->setPaymentOk('FAIL');

                    $order->setState($orderStatusCanceled)->setStatus($orderStatusCanceled);
                    $order->addStatusToHistory($order->getStatus(), json_encode($result));
                    $order->save();

                    $this->checkoutSession->restoreQuote();

                    $message = $this->getRejectMessage($result);
                    $this->messageManager->addError(__($message));

                    return $this->resultRedirectFactory->create()->setPath('checkout/cart');
                }

            } else {

                $result = $this->checkoutSession->getResultWebpay();

                if ($paymentOk == 'SUCCESS') {

                    $message = $this->getSuccessMessage($result);
                    $this->messageManager->addSuccess(__($message));
                    return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');

                } else if ($paymentOk == 'FAIL') {

                    $this->checkoutSession->restoreQuote();
                    $message = $this->getRejectMessage($result);
                    $this->messageManager->addError(__($message));
                    return $this->resultRedirectFactory->create()->setPath('checkout/cart');
                }
            }

        } catch(\Exception $e) {
            $message = 'Error al confirmar transacción: ' . $e->getMessage();
            $this->log->logError($message);
            $this->checkoutSession->restoreQuote();
            $this->messageManager->addError(__($message));
            if ($order != null) {
                $order->setState($orderStatusCanceled)->setStatus($orderStatusCanceled);
                $order->addStatusToHistory($order->getStatus(), $message);
                $order->save();
            }
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
    }

    private function toRedirect($url, $data) {
        $response = $this->resultRawFactory->create();
        $content = "<form action='$url' method='POST' name='webpayForm'>";
        foreach ($data as $name => $value) {
            $content .= "<input type='hidden' name='".htmlentities($name)."' value='".htmlentities($value)."'>";
        }
        $content .= "</form>";
        $content .= "<script language='JavaScript'>"
                ."document.webpayForm.submit();"
                ."</script>";
        $response->setContents($content);
        return $response;
    }

    private function getSuccessMessage($result) {

        if($result->detailOutput->paymentTypeCode == "SI" || $result->detailOutput->paymentTypeCode == "S2" ||
            $result->detailOutput->paymentTypeCode == "NC" || $result->detailOutput->paymentTypeCode == "VC" ) {
            $tipoCuotas = $this->paymentTypeCodearray[$result->detailOutput->paymentTypeCode];
        } else {
            $tipoCuotas = "Sin cuotas";
        }

        if ($result->detailOutput->responseCode == 0) {
            $transactionResponse = "Transacci&oacute;n Aprobada";
        } else {
            $transactionResponse = "Transacci&oacute;n Rechazada";
        }

        if($result->detailOutput->paymentTypeCode == "VD"){
            $paymentType = "Débito";
        } else {
            $paymentType = "Crédito";
        }

		$message = "<h2>Detalles del pago con Webpay</h2>
        <p>
            <br>
            <b>Respuesta de la Transacci&oacute;n: </b>{$transactionResponse}<br>
            <b>C&oacute;digo de la Transacci&oacute;n: </b>{$result->detailOutput->responseCode}<br>
            <b>Monto:</b> $ {$result->detailOutput->amount}<br>
            <b>Order de Compra: </b> {$result->detailOutput->buyOrder}<br>
            <b>Fecha de la Transacci&oacute;n: </b>".date('d-m-Y', strtotime($result->transactionDate))."<br>
            <b>Hora de la Transacci&oacute;n: </b>".date('H:i:s', strtotime($result->transactionDate))."<br>
            <b>Tarjeta: </b>************{$result->cardDetail->cardNumber}<br>
            <b>C&oacute;digo de autorizacion: </b>{$result->detailOutput->authorizationCode}<br>
            <b>Tipo de Pago: </b>{$paymentType}<br>
            <b>Tipo de Cuotas: </b>{$tipoCuotas}<br>
            <b>N&uacute;mero de cuotas: </b>{$result->detailOutput->sharesNumber}
        </p>";
        return $message;
    }

    private function getRejectMessage($result) {
        if  (isset($result->detailOutput)) {
            $message = "<h2>Transacci&oacute;n rechazada con Webpay</h2>
            <p>
                <br>
                <b>Respuesta de la Transacci&oacute;n: </b>{$result->detailOutput->responseCode}<br>
                <b>Monto:</b> $ {$result->detailOutput->amount}<br>
                <b>Order de Compra: </b> {$result->detailOutput->buyOrder}<br>
                <b>Fecha de la Transacci&oacute;n: </b>".date('d-m-Y', strtotime($result->transactionDate))."<br>
                <b>Hora de la Transacci&oacute;n: </b>".date('H:i:s', strtotime($result->transactionDate))."<br>
                <b>Tarjeta: </b>************{$result->cardDetail->cardNumber}<br>
                <b>Mensaje de Rechazo: </b>{$result->detailOutput->responseDescription}
            </p>";
            return $message;
        } else if (isset($result['error'])) {
            $error = $result['error'];
            $detail = isset($result['detail']) ? $result['detail'] : 'Sin detalles';
            $message = "<h2>Transacci&oacute;n fallida con Webpay</h2>
            <p>
                <br>
                <b>Respuesta de la Transacci&oacute;n: </b>{$error}<br>
                <b>Mensaje: </b>{$detail}
            </p>";
            return $message;
        } else {
            $message = "<h2>Transacci&oacute;n Fallida</h2>";
            return $message;
        }
    }

    private function getOrder() {
        $orderId = $this->checkoutSession->getLastOrderId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
    }
}
