<?php

namespace Transbank\Webpay\Model\Config;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface {

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface) {

        $this->_scopeConfigInterface = $scopeConfigInterface;
    }

    public function getConfig() {
        return [
            'pluginConfigWebpay' => array(
                'createTransactionUrl' => 'transaction/createwebpay'
            )
        ];
    }

    public function getPluginConfig() {
        $conf = 'payment/transbank_webpay/security_parameters/';
        $config = array(
			'MODO' => $this->_scopeConfigInterface->getValue($conf.'environment'),
			'PRIVATE_KEY' => $this->_scopeConfigInterface->getValue($conf.'private_key'),
			'PUBLIC_CERT' => $this->_scopeConfigInterface->getValue($conf.'public_cert'),
			'WEBPAY_CERT' => $this->_scopeConfigInterface->getValue($conf.'webpay_cert'),
            'COMMERCE_CODE' => $this->_scopeConfigInterface->getValue($conf.'commerce_code'),
			'URL_RETURN' => 'checkout/transaction/commitwebpay',
			'URL_FINAL' => 'checkout/transaction/commitwebpay',
            'ECOMMERCE' => 'magento',
            'sucefully_pay' => $this->_scopeConfigInterface->getValue($conf.'sucefully_pay'),
            'error_pay' => $this->_scopeConfigInterface->getValue($conf.'error_pay')
        );
        return $config;
    }
}
