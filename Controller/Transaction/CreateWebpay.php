<?php
namespace Transbank\Webpay\Controller\Transaction;

use \Magento\Framework\App\CsrfAwareActionInterface;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\App\Request\InvalidRequestException;

/**
 * Controller for create transaction Webpay
 */

if (interface_exists("\Magento\Framework\App\CsrfAwareActionInterface")) {
    class CreateWebpay extends CreateWebpayM22 implements \Magento\Framework\App\CsrfAwareActionInterface {

        public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
        {
            return null;
        }

        public function validateForCsrf(RequestInterface $request): ?bool
        {
            return true;
        }

    }
} else {
    class CreateWebpay extends CreateWebpayM22 {}
}
