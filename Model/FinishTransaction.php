<?php

/**
* @author     Allware Ltda. (http://www.allware.cl)
* @copyright  2016 Transbank S.A. (http://www.transbank.cl)
* @license    GNU LGPL
* @version    3.1.1
*/

namespace Transbank\Webpay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Transbank\Webpay\Model\Libwebpay\WebpayNormal;
use Transbank\Webpay\Model\Libwebpay\WebpayConfig;

class FinishTransaction
{
  public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Checkout\Model\Session $session
  ) {
    $this->_scopeConfig = $scopeConfig;
    $this->_session = $session;
  }

  public function getTransactionNumber(){
    $result == null;
    return $result;
  }
}
