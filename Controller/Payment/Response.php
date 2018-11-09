<?php

  namespace Transbank\Webpay\Controller\Implement;

/**
 *
 */
class CallBackURL extends \Magento\Framework\App\Action\Action
{

  public function __construct(
        \Transbank\Webpay\Model\FinishTransaction $customer,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_customer = $customer;
        $this->_session  = $session;
        $this->_scopeConfig    = $scopeConfig;
        $this->_messageManager = $context->getMessageManager();
        $this->_logger = $logger;

        parent::__construct($context);
    }

    public function execute(){

      $this->_logger->info("[Allware] ".json_encode($_POST));

    }
}
