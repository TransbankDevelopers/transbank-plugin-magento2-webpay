<?php
namespace Transbank\Webpay\Controller\Implement;

use Transbank\Webpay\Model\Libwebpay\LogHandler;

class Finish extends \Magento\Framework\App\Action\Action {

    private $paymentTypeCodearray = array(
        "VD" => "Venta Debito",
        "VN" => "Venta Normal",
        "VC" => "Venta en cuotas",
        "SI" => "3 cuotas sin interés",
        "S2" => "2 cuotas sin interés",
        "NC" => "N cuotas sin interés",
    );

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $session,
        \Transbank\Webpay\Model\Config\ConfigProvider $configProvider) {

        parent::__construct($context);

        $this->_session = $session;
        $this->_messageManager = $context->getMessageManager();
        $this->_configProvider = $configProvider;

        $this->_logHandler = new LogHandler();
    }

    /**
     * @Override
     */
    public function execute() {

        if (!isset($_POST['token_ws'])) {
            $tokenWs = $_GET['token_ws'];
        } else {
            $tokenWs = $_POST['token_ws'];
        }

        $this->_logHandler->logInfo('4- finish token: ' . $tokenWs . ', ' . $this->_session->getTokenWs());

        if($tokenWs == $this->_session->getTokenWs()){
            $result = $this->_session->getResultWebpay();

            $this->_logHandler->logInfo('5- result: ' . json_encode($result));

            if (isset($result->buyOrder) && isset($result->detailOutput) && $result->detailOutput->responseCode == 0) {
                $response = $this->authorized($result);
                $this->_messageManager->addSuccess(__($response));
            }else{
                $response = $this->reject($result);
                $this->_messageManager->addError(__($response));
            }
        }

        $this->_logHandler->logInfo('6- response: ' . $response);

        return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
    }

    private function authorized($result) {

        if($result->detailOutput->paymentTypeCode == "SI" || $result->detailOutput->paymentTypeCode == "S2" ||
            $result->detailOutput->paymentTypeCode == "NC" || $result->detailOutput->paymentTypeCode == "VC" ) {
            $tipo_cuotas = $this->paymentTypeCodearray[$result->detailOutput->paymentTypeCode];
        } else {
            $tipo_cuotas = "Sin cuotas";
        }

        $this->_logHandler->logInfo('tipo_cuotas: ' . $tipo_cuotas);

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
            <b>N&uacute;mero de cuotas: </b>{$tipo_cuotas}
        </p>";
        return $message;
    }

    private function reject($result) {
        $message = "<h2>Transacci&oacute;n Rechazada</h2>
        <p>
            <br>
            <b>Respuesta de la Transacci&oacute;n: </b>{$result->detailOutput->responseCode}<br>
            <b>Monto:</b> $ {$result->detailOutput->amount}<br>
            <b>Order de Compra: </b> {$result->detailOutput->buyOrder}<br>
            <b>Fecha de la Transacci&oacute;n: </b>".date('d-m-Y', strtotime($result->transactionDate))."<br>
            <b>Hora de la Transacci&oacute;n: </b>".date('H:i:s', strtotime($result->transactionDate))."<br>
            <b>Tarjeta: </b>************{$result->cardDetail->cardNumber}<br>
            <b>Mensaje de Rechazo: </b>{$result->responseDescription}
        </p>";
        return $message;
    }
}
