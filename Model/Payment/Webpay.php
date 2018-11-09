<?php
namespace Transbank\Webpay\Model\Payment;

class Webpay extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = "webpay";
    protected $_isOffline = false;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }
}
