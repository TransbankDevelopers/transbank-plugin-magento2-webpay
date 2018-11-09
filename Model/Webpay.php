<?php

namespace Transbank\Webpay\Model;

class Webpay extends \Magento\Payment\Model\Method\AbstractMethod
{
  protected $_code = 'webpay';

  protected $_isOffline = false;
}
