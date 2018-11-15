<?php

namespace Transbank\Webpay\Model\Config;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface {

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface) {

        $conf = 'payment/transbank_webpay/security_parameters/';

        $this->_config = array(
			'MODO' => $scopeConfigInterface->getValue($conf.'environment'),
			'PRIVATE_KEY' => $scopeConfigInterface->getValue($conf.'private_key'),
			'PUBLIC_CERT' => $scopeConfigInterface->getValue($conf.'public_cert'),
			'WEBPAY_CERT' => $scopeConfigInterface->getValue($conf.'webpay_cert'),
			'COMMERCE_CODE' => $scopeConfigInterface->getValue($conf.'commerce_code'),
			'URL_RETURN' => 'checkout/Implement/CallBackURL',
			'URL_FINAL' => 'checkout/Implement/Finish',
            'ECOMMERCE' => 'magento',
            'sucefully_pay' => $scopeConfigInterface->getValue($conf.'sucefully_pay'),
            'error_pay' => $scopeConfigInterface->getValue($conf.'error_pay')
		);
    }

    public function getConfig() {
        return $this->_config;
    }
}
