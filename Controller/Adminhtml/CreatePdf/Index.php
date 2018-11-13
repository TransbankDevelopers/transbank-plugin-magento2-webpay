<?php
namespace Transbank\Webpay\Controller\Adminhtml\CreatePdf;

use Transbank\Webpay\Model\Libwebpay\HealthCheck;
use Transbank\Webpay\Model\Libwebpay\ReportPdfLog;

class Index extends \Magento\Backend\App\Action {

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @Override
     */
    public function execute() {

        $config = array(
            "ECOMMERCE" => 'magento',
            "MODO" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/environment'),
            "PRIVATE_KEY" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/private_key'),
            "PUBLIC_CERT" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/public_cert'),
            "WEBPAY_CERT" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/webpay_cert'),
            "COMMERCE_CODE" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/commerce_code')
        );

        $healthcheck = new HealthCheck($config);
        $json = $healthcheck->printFullResume();
        $temp = json_decode($json);

        $document = $_GET["document"];
        if ($document == "report") {
            unset($temp->php_info);
        } else {
            $temp = array('php_info' => $temp->php_info);
        }
        $json = json_encode($temp);
        $rl = new ReportPdfLog($document);
        $rl->getReport($json);
    }
}
