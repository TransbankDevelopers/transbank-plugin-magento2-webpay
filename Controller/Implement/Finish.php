<?php
namespace Transbank\Webpay\Controller\Implement;

class Finish extends \Magento\Framework\App\Action\Actionc {

    public function __construct(
      \Transbank\Webpay\Model\getTransactionResult $customer,
      \Magento\Checkout\Model\Session $session,
      \Magento\Framework\App\Action\Context $context,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Psr\Log\LoggerInterface $logger
    ) {
        $this->_customer = $customer;
        $this->_session  = $session;
        $this->_scopeConfig    = $scopeConfig;
        $this->_messageManager = $context->getMessageManager();
        $this->_logger = $logger;
        parent::__construct($context);
    }

    public function execute() {
        $result = $this->_session->getResultWebpay();
        $result = $_POST;
        if(array_key_exists('TBK_ORDEN_COMPRA',$result)){
            $result = $_POST;
            $response = $this->annulled($result);
            $this->_messageManager->addError(__($response));
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $orderDatamodel = $objectManager->get('Magento\Sales\Model\Order')->getCollection()->getLastItem();
            $orderId = $orderDatamodel->getId();
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
            $payError = $this->_scopeConfig->getValue('payment/webpay/security_parameters/error_pay');
            $order->setState($payError)->setStatus($payError);
            $order->save();
        } else {
            $result = $this->_session->getResultWebpay();
            if (($result['VCI'] == 'TSY' || $result['VCI'] == 'A'  || $result['VCI'] == "") && $result['detailOutput']['responseCode'] == 0) {
                $response = $this->authorized($result);
                $this->_messageManager->addSuccess(__($response));
            }else{
                $result = $_POST;
                $response = $this->reject($result);
                $this->_messageManager->addError(__($response));
            }
        }

        return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
    }

    public function authorized($result) {
		$message = "<img src='https://www.transbank.cl/public/img/Logo_Webpay3-01-50x50.png' alt='WebPay' title='WebPay' align='middle' style='border: 1px solid #EEEEEE;' /><h2>Detalles del Pago</h2>
        <p>
        <br>
        <b>Respuesta de la Transacci&oacute;n: </b>{$result['detailOutput']['responseCode']}<br>
        <b>Monto:</b> $ {$result['detailOutput']['amount']}.-<br>
        <b>Order de Compra: </b> {$result['detailOutput']['buyOrder']}<br>
        <b>Fecha de la Transacci&oacute;n: </b>".date('d-m-Y', strtotime($result['transactionDate']))."<br>
        <b>Hora de la Transacci&oacute;n: </b>".date('H:i:s', strtotime($result['transactionDate']))."<br>
        <b>Tarjeta: </b>************{$result['cardDetail']['cardNumber']}<br>
        <b>C&oacute;digo de autorizacion: </b>{$result['detailOutput']['authorizationCode']}
        </p>";
        return $message;
    }

    public function reject($result) {
        $message =  "<img src='https://www.transbank.cl/public/img/Logo_Webpay3-01-50x50.png' alt='WebPay' title='WebPay' align='middle' style='border: 1px solid #EEEEEE;' /> <h2>Transacci&oacute;n Rechazada</h2>
        <p>
            <br>
            <b>Respuesta de la Transacci&oacute;n: </b>{$result['responseCode']}<br>
            <b>Monto:</b> $ {$result['amount']}.-<br>
            <b>Order de Compra: </b> {$result['buyOrder']}<br>
            <b>Fecha de la Transacci&oacute;n: </b>".date('d-m-Y', strtotime($result['transactionDate']))."<br>
            <b>Hora de la Transacci&oacute;n: </b>".date('H:i:s', strtotime($result['transactionDate']))."<br>
            <b>Tarjeta: </b>************{$result['cardNumber']}<br>
            <b>Mensaje de Rechazo: </b>{$result['responseDescription']}
        </p>";
        return $message;
    }

    public function annulled($result) {
        $message =  "<img src='https://www.transbank.cl/public/img/Logo_Webpay3-01-50x50.png' alt='WebPay' title='WebPay' align='middle' style='border: 1px solid #EEEEEE;' /> <h2>Transacci&oacute;n Rechazada</h2>
        <p>
            <br>
            <b>Respuesta de la Transacci&oacute;n: Transacci&oacute;n cancelada por cliente </b><br>
            <b>Order de Compra: </b> {$result['TBK_ORDEN_COMPRA']}<br>
        </p>";
        return $message;
    }
}
