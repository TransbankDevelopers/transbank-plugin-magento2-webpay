<?php
namespace Transbank\Webpay\Controller\Adminhtml\CallLogHandler;

use Transbank\Webpay\Model\Libwebpay\LogHandler;

class Index extends \Magento\Backend\App\Action {

    public function __construct(\Magento\Backend\App\Action\Context $context) {
        parent::__construct($context);
    }

    /**
     * @Override
     */
    public function execute() {
        $logHandler = new LogHandler();
        if ($_POST["action_check"] == 'true') {
            $logHandler->setLockStatus(true);
            $logHandler->setparamsconf($_POST['days'], $_POST['size']);
        } else {
            $logHandler->setLockStatus(false);
        }
    }
}
?>
