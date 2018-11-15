<?php
namespace Transbank\Webpay\Controller\Adminhtml\CreatePdf;

use Transbank\Webpay\Model\Libwebpay\HealthCheck;
use Transbank\Webpay\Model\Libwebpay\ReportPdfLog;

class Index extends \Magento\Backend\App\Action {

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Transbank\Webpay\Model\Config\ConfigProvider $configProvider) {
        parent::__construct($context);
        $this->_configProvider = $configProvider;
    }

    /**
     * @Override
     */
    public function execute() {

        $config = $this->_configProvider->getConfig();

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
