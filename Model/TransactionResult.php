<?php

/**
* @author     Allware Ltda. (http://www.allware.cl)
* @copyright  2017 Transbank S.A. (http://www.transbank.cl)
* @license    GNU LGPL
* @version   3.1.1
*/

namespace Transbank\Webpay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class TransactionResult
{
  public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Checkout\Model\Session $session
  ) {
    $this->_scopeConfig = $scopeConfig;
    $this->_session = $session;
  }

  public function getTransactionNumber(){
    $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
    $logger = new \Zend\Log\Logger();
    $logger->addWriter($writer);
    $occ = $_POST['occ'];
    $externalUniqueNumber = $_POST['externalUniqueNumber'];
  }
}
