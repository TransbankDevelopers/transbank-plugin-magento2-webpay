<?php
namespace Transbank\Webpay\Controller\Adminhtml\Request;

use Transbank\Webpay\Model\Libwebpay\HealthCheck;

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
        if($_POST['type'] == 'checkInit') {
            try {

                $config = array(
                    "ECOMMERCE" => 'magento',
                    "MODO" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/environment'),
                    "PRIVATE_KEY" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/private_key'),
                    "PUBLIC_CERT" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/public_cert'),
                    "WEBPAY_CERT" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/webpay_cert'),
                    "COMMERCE_CODE" => $this->_scopeConfig->getValue('payment/webpay/security_parameters/commerce_code')
                );

                $healthcheck = new HealthCheck($config);
                $response = $healthcheck->getInitTransaction();

                echo json_encode(['success' => true, 'msg' => json_decode($response)]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
            }
        }
    }
}
