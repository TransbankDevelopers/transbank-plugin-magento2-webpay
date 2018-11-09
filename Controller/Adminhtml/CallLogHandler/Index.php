<?php
  namespace Transbank\Webpay\Controller\Adminhtml\CallLogHandler;
  use Magento\Backend\App\Action;
  use Magento\Backend\App\Action\Context;
  use Transbank\Webpay\Model\Libwebpay\LogHandler;
  class Index extends Action
  {
    /**
    * @var \Magento\Framework\View\Result\PageFactory
    */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
         parent::__construct($context);
      //   $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Load the page defined in view/adminhtml/layout/exampleadminnewpage_helloworld_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
      if (!isset($_COOKIE["action_check"])) {
      	die;
      }
      $log = new loghandler('magento');

      if ($_COOKIE["action_check"] == 'true') {
      	$log->setLockStatus(true);
      	$log->setnewconfig($_COOKIE['days'] , $_COOKIE['size']);
      }
      else
      	$log->setLockStatus(false);

      echo "<script>window.close();</script>";
   }
  }
?>
