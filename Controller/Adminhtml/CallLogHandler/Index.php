<?php
namespace Transbank\Webpay\Controller\Adminhtml\CallLogHandler;

use Transbank\Webpay\Model\Libwebpay\LogHandler;

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
        if (!isset($_COOKIE["action_check"])) {
            die;
        }
        $log = new LogHandler();
        if ($_COOKIE["action_check"] == 'true') {
            $log->setLockStatus(true);
            $log->setparamsconf($_COOKIE['days'] , $_COOKIE['size']);
        } else {
            $log->setLockStatus(false);
        }
        echo "<script>window.close();</script>";
    }
}
?>
