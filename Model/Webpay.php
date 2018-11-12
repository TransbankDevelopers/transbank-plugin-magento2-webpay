<?php
namespace Transbank\Webpay\Model;

class Webpay extends \Magento\Payment\Model\Method\AbstractMethod {

    const CODE = 'transbank_webpay';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Array of currency support
     */
    protected $_supportedCurrencyCodes = array('CLP');

    //protected $_isOffline = false;
    protected $_isGateway = true;
}
