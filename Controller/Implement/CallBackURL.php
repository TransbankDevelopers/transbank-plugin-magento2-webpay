<?php
namespace Transbank\Webpay\Controller\Implement;

class CallBackURL extends \Magento\Framework\App\Action\Action {

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

        if (!isset($_POST['token_ws'])) {
            $token = $_GET['token_ws'];
        } else {
            $token = $_POST['token_ws'];
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderDatamodel = $objectManager->get('Magento\Sales\Model\Order')->getCollection()->getLastItem();
        $orderId   =   $orderDatamodel->getId();
        $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);

        $this->_session->setToken($token);

        $result = $this->_customer->getTransactionResult($token);
        $paySucefully = $this->_scopeConfig->getValue('payment/webpay/security_parameters/sucefully_pay');
        $payError = $this->_scopeConfig->getValue('payment/webpay/security_parameters/error_pay');

        $result = json_decode(json_encode($result), true);

        $this->_logger->info(json_encode($result));

        if (($result['VCI'] == 'TSY' ||$result['VCI'] == 'A' || $result['VCI'] == "") && $result['detailOutput']['responseCode'] == 0) {
            $this->_session->setResultWebpay($result);
            $order->setState($paySucefully)->setStatus($paySucefully);
            $order->save();
            $this->_session->getQuote()->setIsActive(false)->save();
            $this->redirect($result['urlRedirection'],array('token_ws' => $token));
        } else {
            $this->_session->setResultWebpay($result);
            $order->setState($payError)->setStatus($payError);
            $order->save();
            $send = array(
                'responseCode' => $result['detailOutput']['responseCode'],
                'responseDescription' => $result['detailOutput']['responseDescription'],
                'amount' => $result['detailOutput']['amount'],
                'transactionDate' => $result['transactionDate'],
                'cardNumber' => $result['cardDetail']['cardNumber'],
                'buyOrder' => $result['buyOrder']
            );
            $this->redirect($result['urlRedirection'],$send);
            $this->_session->restoreQuote();
        }
    }

    public function redirect($url, $data) {
        echo "<form action='$url' method='POST' name='webpayForm'>";
        foreach ($data as $name => $value) {
            echo "<input type='hidden' name='".htmlentities($name)."' value='".htmlentities($value)."'>";
        }
        echo "</form>";
        echo "<script language='JavaScript'>"
                ."document.webpayForm.submit();"
                ."</script>";
    }
}
