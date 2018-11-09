<?php
namespace Transbank\Webpay\Model;

class Core extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'webpay';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = false;

}
