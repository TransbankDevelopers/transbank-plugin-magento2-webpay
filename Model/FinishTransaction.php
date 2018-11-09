<?php
namespace Transbank\Webpay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class FinishTransaction {

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
