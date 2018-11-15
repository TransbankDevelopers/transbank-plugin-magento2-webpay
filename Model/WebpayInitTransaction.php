<?php
namespace Transbank\Webpay\Model;

use Transbank\Webpay\Model\Libwebpay\TransbankSdkWebpay;
use Transbank\Webpay\Model\Libwebpay\LogHandler;

class WebpayInitTransaction implements \Magento\Checkout\Model\ConfigProviderInterface {

	public function __construct(
		\Magento\Checkout\Model\Cart $cart,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Checkout\Model\Session $session,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Transbank\Webpay\Model\Config\ConfigProvider $configProvider) {

        $this->_context = $context;
		$this->_scopeConfig  = $scopeConfig;
		$this->_storeManager = $storeManager;
		$this->_cart = $cart;
        $this->_session = $session;
		$this->_configProvider = $configProvider;
	}

	public function getConfig(){
		return [
		    'webpayInitTransaction' => $this->webpayInitTransaction(),
		];
	}

	private function webpayInitTransaction() {
        try {

            $data = $this->_cart->getQuote()->getData();
            $entityId = (string)$data["entity_id"];

            $reservBuyOrder = $this->_session->getQuote()->reserveOrderId();
            $reservedOrderId = $this->_session->getQuote()->getReservedOrderId();
            $saveReserveBuyOrder = $this->_session->getQuote()->setReservedOrderId($reservedOrderId)->save();
            $orderId = $this->_session->getQuote()->getReservedOrderId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
            $grandTotal = round($this->_cart->getQuote()->getGrandTotal());

            // enviar parametros a variable de sesion para recuperar
            $this->_session->setGrandTotal($grandTotal);
            $this->_session->setEntityId($entityId);
            $this->_session->setOrderId($orderId);

            $logHandler = new LogHandler();
            $logHandler->logInfo('1- orderId: ' . $orderId . ', grandTotal: ' . $grandTotal . ', entityId: ' . $entityId);

            $baseUrl = $this->_storeManager->getStore()->getBaseUrl();

            $config = $this->_configProvider->getConfig();

            $returnUrl = $baseUrl . $config['URL_RETURN'];
            $finalUrl = $baseUrl . $config['URL_FINAL'];

            $transbankSdkWebpay = new TransbankSdkWebpay($config);
            $response = $transbankSdkWebpay->initTransaction($grandTotal, $entityId, $orderId, $returnUrl, $finalUrl);

            if (isset($response['token_ws'])) {
                $tokenWs = $response['token_ws'];
                $this->_session->setTokenWs($tokenWs);
                $logHandler->logInfo('token_ws: ' . $tokenWs);
            }

            return $response;

        } catch(Exception $ex) {
            return array('error' => $ex->getMessage());
        }
	}
}
