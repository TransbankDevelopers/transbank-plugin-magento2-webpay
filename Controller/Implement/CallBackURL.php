<?php
namespace Transbank\Webpay\Controller\Implement;

use Transbank\Webpay\Model\Libwebpay\TransbankSdkWebpay;
use Transbank\Webpay\Model\Libwebpay\LogHandler;

class CallBackURL extends \Magento\Framework\App\Action\Action {

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $session,
        \Transbank\Webpay\Model\Config\ConfigProvider $configProvider) {

        parent::__construct($context);

        $this->_session = $session;
        $this->_messageManager = $context->getMessageManager();
        $this->_configProvider = $configProvider;
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

        $grandTotal = $this->_session->getGrandTotal();
        $entityId = $this->_session->getEntityId();
        $orderId = $this->_session->getOrderId();

        $logHandler = new LogHandler();
        $logHandler->logInfo('2- orderId: ' . $orderId . ', grandTotal: ' . $grandTotal . ', entityId: ' . $entityId);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderDatamodel = $objectManager->get('Magento\Sales\Model\Order')->getCollection()->getLastItem();
        $orderId = $orderDatamodel->getId();
        $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);

        $result = array();

        $config = $this->_configProvider->getConfig();

        try {
            $transbankSdkWebpay = new TransbankSdkWebpay($config);
            $result = $transbankSdkWebpay->commitTransaction($tokenWs);
        } catch (Exception $e) {
            $logHandler->logError('Error en confirmar transaccion: ' . $e->getMessage());
        }

        $logHandler->logInfo('3- result: ' . json_encode($result));

        if (isset($result->buyOrder) && isset($result->detailOutput) && $result->detailOutput->responseCode == 0) {
            $this->_session->setResultWebpay($result);
            $orderStatus = $config['sucefully_pay'];
            $order->setState($orderStatus)->setStatus($orderStatus);
            $order->save();
            $this->_session->getQuote()->setIsActive(false)->save();
            $this->toRedirect($result->urlRedirection, array('token_ws' => $tokenWs));
        } else {
            $orderStatus = $config['error_pay'];
            $order->setState($orderStatus)->setStatus($orderStatus);
            $order->save();
            $this->_session->restoreQuote();
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
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
    }
}
