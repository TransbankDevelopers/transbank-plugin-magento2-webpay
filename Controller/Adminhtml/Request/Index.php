<?php
  namespace Transbank\Webpay\Controller\Adminhtml\Request;

  use Magento\Backend\App\Action;
  use Magento\Backend\App\Action\Context;
  use Transbank\Webpay\Model\Libwebpay\HealthCheck;

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
          
		  $type = $_POST['type'];

		  switch($type)
		  {
			  case 'checkInit':
			  
				$response = [];
			  
				$arg = [];
			  
				$arg = [
					'MODO' 			=> $_POST['MODE'],
					'COMMERCE_CODE'	=> $_POST['C_CODE'],
					'PUBLIC_CERT'   => $_POST['PUBLIC_CERT'],
					'PRIVATE_KEY'	=> $_POST['PRIVATE_KEY'],
					'WEBPAY_CERT'	=> $_POST['WEBPAY_CERT'],
					'ECOMMERCE'     => 'magento'
				];
				
				$healthcheck = new HealthCheck($arg);
			  
				try
				{
					
					$response = $healthcheck->getInitTransaction();
					
					echo json_encode(['success' => true, 'msg' => json_decode($response)]);
				}
				catch (Exception $e)
				{
					echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
				}
			  
			  break;
		  }	
		  
      }
  }
