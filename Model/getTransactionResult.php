<?php

namespace Transbank\Webpay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

use Transbank\Webpay\Model\Libwebpay\WebpayConfig;
use Transbank\Webpay\Model\Libwebpay\WebpayNormal;

class getTransactionResult implements ConfigProviderInterface
{
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
        ) {
        $this->_scopeConfig  = $scopeConfig;
        $this->_storeManager = $storeManager;

        $this->config = array(
            "MODO" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/environment'),
            "PRIVATE_KEY" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/private_key'),
            "PUBLIC_CERT" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/public_cert'),
            "WEBPAY_CERT" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/webpay_cert'),
            "COMMERCE_CODE" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/commerce_code'),
            "URL_RETURN" => $this->_storeManager->getStore()->getBaseUrl()."webpay/Implement/CallBackURL",
            "URL_FINAL" => $this->_storeManager->getStore()->getBaseUrl()."webpay/Implement/CallBackFinal",
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
    public function getConfig()
    {
        return [
             'getTransactionResult' => $this->getTransactionResult()
           ];
    }


    public function getTransactionResult($token)
    {
        try {

            $this->webpayconfig = new WebPayConfig($this->config);
            $this->webpay = new WebPayNormal($this->webpayconfig);

            $result = $this->webpay->getTransactionResult($token);
        } catch (Exception $e) {
            $result[] = 'Error!:';
            $result[] = $e;
        }

        return $result;
    }
}
