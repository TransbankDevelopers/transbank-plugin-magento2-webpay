<?php
namespace Transbank\Webpay\Controller\Adminhtml\CreatePdf;

use Transbank\Webpay\Model\Libwebpay\HealthCheck;
use Transbank\Webpay\Model\Libwebpay\ReportPdfLog;

class Index extends \Magento\Backend\App\Action {

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(\Magento\Backend\App\Action\Context $context) {
        parent::__construct($context);
    }

    /**
     * @Override
     */
    public function execute() {
        if (!isset($_COOKIE["ambient"])) {
            die;
        }
        $arg = array('MODO' => $_COOKIE["ambient"],
                    'COMMERCE_CODE' => $_COOKIE["storeID"],
                    'PUBLIC_CERT' => $_COOKIE["certificate"],
                    'PRIVATE_KEY' => $_COOKIE["secretCode"],
                    'WEBPAY_CERT' => $_COOKIE["certificateTransbank"],
                    'ECOMMERCE' => 'magento');
        $document = $_COOKIE["document"];

        setcookie("ambient", "", time()-3600, '/');
        setcookie("storeID", "", time()-3600, '/');
        setcookie("certificate", "", time()-3600, '/');
        setcookie("secretCode", "", time()-3600, '/');
        setcookie("certificateTransbank", "", time()-3600, '/');
        setcookie("document", "", time()-3600, '/');

        unset($_COOKIE['ambient']);
        unset($_COOKIE['storeID']);
        unset($_COOKIE['certificate']);
        unset($_COOKIE['secretCode']);
        unset($_COOKIE['certificateTransbank']);
        unset($_COOKIE['document']);

        $healthcheck = new HealthCheck($arg);
        $json = $healthcheck->printFullResume();
        $temp = json_decode($json);
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
