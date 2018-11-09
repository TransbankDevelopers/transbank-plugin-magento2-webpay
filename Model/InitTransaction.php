<?php
namespace Transbank\Webpay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Transbank\Webpay\Model\Libwebpay\WebpayNormal;

class InitTransaction implements ConfigProviderInterface {

	public function __construct(
		\Magento\Checkout\Model\Cart $cart,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Checkout\Model\Session $session,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	) {
		$this->_scopeConfig  = $scopeConfig;
		$this->_storeManager = $storeManager;
		$this->_cart = $cart;
		$this->_session = $session;
		$this->config = array(
			"MODO" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/environment'),
			"PRIVATE_KEY" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/private_key'),
			"PUBLIC_CERT" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/public_cert'),
			"WEBPAY_CERT" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/webpay_cert'),
			"COMMERCE_CODE" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/commerce_code'),
			"URL_RETURN" => $this->_storeManager->getStore()->getBaseUrl()."webpay/Implement/CallBackURL",
			"URL_FINAL" => $this->_storeManager->getStore()->getBaseUrl()."webpay/Implement/Finish",
			"ECOMMERCE" => "magento",
			"VENTA_DESC" => array(
				"VD" => "Venta Deb&iacute;to",
				"VN" => "Venta Normal",
				"VC" => "Venta en cuotas",
				"SI" => "3 cuotas sin inter&eacute;s",
				"S2" => "2 cuotas sin inter&eacute;s",
				"NC" => "N cuotas sin inter&eacute;s",
			),
		);
	}

	public function getConfig(){
		return [
		    'initTransaction' => $this->initTransaction(),
		];
	}

	private function initTransaction() {
		try {

			$entityId = (string)$getData["entity_id"];

			/*
			Se reserva orden de compra.
			*/
			$reservBuyOrder=$this->_session->getQuote()->reserveOrderId();
			$ORDEN_PRE=$this->_session->getQuote()->getReservedOrderId();
			$saveReserveBuyOrder=$this->_session->getQuote()->setReservedOrderId($ORDEN_PRE)->save();
			$orderId=$this->_session->getQuote()->getReservedOrderId();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
			$getData = $this->_cart->getQuote()->getData();
			$grandTotal = round($this->_cart->getQuote()->getGrandTotal());

			// enviar parametros a variable de sesion para recuperar
			$this->_session->setGrandTotal($grandTotal);
            $this->_session->setEntityId($entityId);

            $webpay = new WebPayNormal($this->config);
			$result = $webpay->initTransaction($grandTotal, $entityId, $orderId, $this->config['URL_FINAL']);
			return json_encode($result);
		} catch (Exception $e) {
		}
	}
}
