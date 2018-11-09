<?php

namespace Transbank\Webpay\Controller\Payment;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Display extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context)
    {
        return parent::__construct($context);
    }

    public function execute()
    {
        echo 'Hello World';
        exit;
    }
}