<?php
  namespace Transbank\Webpay\Controller\Adminhtml\CreatePdf;

use Magento\Backend\App\Action;
  use Magento\Backend\App\Action\Context;
  use Transbank\Webpay\Model\Libwebpay\HealthCheck;
  use Transbank\Webpay\Model\Libwebpay\tcpdf\reportPDFlog;

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
          if (!isset($_COOKIE["ambient"])) {
              die;
          }
          // var_dump($_COOKIE);
          $ecommerce = 'magento';
          $arg =  array('MODO' => $_COOKIE["ambient"],
                      'COMMERCE_CODE' => $_COOKIE["storeID"],
                      'PUBLIC_CERT' => $_COOKIE["certificate"],
                      'PRIVATE_KEY' => $_COOKIE["secretCode"],
                      'WEBPAY_CERT' => $_COOKIE["certificateTransbank"],
                      'ECOMMERCE' => $ecommerce);
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
          $json =$healthcheck->printFullResume();
          $rl = new reportPDFlog($ecommerce, $document);
          $temp = json_decode($json);
          if ($document == "report") {
              unset($temp->php_info);
          } else {
              $temp = array('php_info' => $temp->php_info);
          }
          $json = json_encode($temp);
          $rl->getReport($json);
      }
  }
