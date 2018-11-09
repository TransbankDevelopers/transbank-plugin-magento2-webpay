<?php
namespace Transbank\Webpay\Controller\Adminhtml\Request;

use Transbank\Webpay\Model\Libwebpay\HealthCheck;

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
     * Load the page defined in view/adminhtml/layout/exampleadminnewpage_helloworld_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {

        if (!isset($_COOKIE["ambient"])) {
            die;
        }

        $type = $_POST['type'];

        if($type == 'checkInit') {
            try {

                $config = array(
                    'MODO' => $_POST['MODE'],
                    'COMMERCE_CODE'	=> $_POST['C_CODE'],
                    'PUBLIC_CERT' => $_POST['PUBLIC_CERT'],
                    'PRIVATE_KEY' => $_POST['PRIVATE_KEY'],
                    'WEBPAY_CERT' => $_POST['WEBPAY_CERT'],
                    'ECOMMERCE' => 'magento'
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
