<?php
namespace Transbank\Webpay\Controller\Transaction;

use Transbank\Webpay\Model\Libwebpay\TransbankSdkWebpay;
use Transbank\Webpay\Model\Libwebpay\LogHandler;

use \Magento\Sales\Model\Order;

/**
 * Controller for commit transaction Webpay
 */
class CommitWebpay extends \Magento\Framework\App\Action\Action {

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
                                \Transbank\Webpay\Model\Config\ConfigProvider $configProvider) {

        parent::__construct($context);

        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $context->getMessageManager();
        $this->configProvider = $configProvider;
        $this->log = new LogHandler();
    }

    /**
     * @Override
     */
    public function execute() {

        $tokenWs = isset($_POST['token_ws']) ? $_POST['token_ws'] : null;

        if($tokenWs != $this->checkoutSession->getTokenWs()) {
            $this->checkoutSession->restoreQuote();
            $this->messageManager->addError(__('Trasacción inválida'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $paymentOk = $this->checkoutSession->getPaymentOk();

        $order = $this->getOrder();

        if ($paymentOk  == 'WAITING') {

            $order = $this->getOrder();

            $quoteId = $this->checkoutSession->getLastQuoteId();
            $orderId = $this->checkoutSession->getLastOrderId();
            $grandTotal = $this->checkoutSession->getGrandTotal();

            $this->log->logInfo('2- quoteId: ' . $quoteId . ', orderId: ' . $orderId . ', grandTotal: ' . $grandTotal);

            $result = array();

            $config = $this->configProvider->getPluginConfig();

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

                $orderStatus = $config['error_pay'];
                $order->setState($orderStatus)->setStatus($orderStatus);
                $order->addStatusToHistory($order->getStatus(), json_encode($result));
                $order->save();

                $this->checkoutSession->restoreQuote();

                $message = $this->getRejectMessage($result);
                $this->messageManager->addError(__($message));

                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }

        } else {

            $result = $this->checkoutSession->getResultWebpay();

            if ($paymentOk  == 'SUCCESS') {

                $message = $this->getSuccessMessage($result);
                $this->messageManager->addSuccess(__($message));
                return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');

            } else if ($paymentOk  == 'FAIL') {

                $message = $this->getRejectMessage($result);
                $this->messageManager->addError(__($message));
                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }
        }
    }

    private function toRedirect($url, $data) {
        echo "<form action='$url' method='POST' name='webpayForm'>";
        foreach ($data as $name => $value) {
            echo "<input type='hidden' name='".htmlentities($name)."' value='".htmlentities($value)."'>";
        }
        echo "</form>";
        echo "<script language='JavaScript'>"
                ."document.webpayForm.submit();"
                ."</script>";
        return true;
    }

    private function getSuccessMessage($result) {

        if($result->detailOutput->paymentTypeCode == "SI" || $result->detailOutput->paymentTypeCode == "S2" ||
            $result->detailOutput->paymentTypeCode == "NC" || $result->detailOutput->paymentTypeCode == "VC" ) {
            $tipoCuotas = $this->paymentTypeCodearray[$result->detailOutput->paymentTypeCode];
        } else {
            $tipoCuotas = "Sin cuotas";
        }

		$message = "<h2>Detalles del Pago</h2>
        <p>
            <br>
            <b>Respuesta de la Transacci&oacute;n: </b>{$result->detailOutput->responseCode}<br>
            <b>Monto:</b> $ {$result->detailOutput->amount}<br>
            <b>Order de Compra: </b> {$result->detailOutput->buyOrder}<br>
            <b>Fecha de la Transacci&oacute;n: </b>".date('d-m-Y', strtotime($result->transactionDate))."<br>
            <b>Hora de la Transacci&oacute;n: </b>".date('H:i:s', strtotime($result->transactionDate))."<br>
            <b>Tarjeta: </b>************{$result->cardDetail->cardNumber}<br>
            <b>C&oacute;digo de autorizacion: </b>{$result->detailOutput->authorizationCode}<br>
            <b>N&uacute;mero de cuotas: </b>{$tipoCuotas}
        </p>";
        return $message;
    }

    private function getRejectMessage($result) {
        $message = "<h2>Transacci&oacute;n Rechazada</h2>
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
    }

    private function getOrder() {
        $orderId = $this->checkoutSession->getLastOrderId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
    }
}
