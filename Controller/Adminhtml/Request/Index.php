<?php
namespace Transbank\Webpay\Controller\Adminhtml\Request;

use Transbank\Webpay\Model\Libwebpay\HealthCheck;

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
        if($_POST['type'] == 'checkInit') {
            try {

                $config = $this->_configProvider->getConfig();
                $healthcheck = new HealthCheck($config);
                $response = $healthcheck->getInitTransaction();

                echo json_encode(['success' => true, 'msg' => json_decode($response)]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
            }
        }
    }
}
