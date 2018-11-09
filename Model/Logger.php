<?php

namespace Transbank\Webpay\Model;

/**
 * 
 */
class Logger
{
    public static function log($msg, $file, $priority = 'debug')
    {
        switch ($priority) {
        case 'error':
          $prior = \Zend\Log\Logger::ERR;
          break;
        case 'warning':
            $prior = \Zend\Log\Logger::WARN;
            break;
        case 'info':
            $prior = \Zend\Log\Logger::INFO;
            break;

        default:
          $prior = \Zend\Log\Logger::DEBUG;
          break;
      }

        $writer = new \Zend\Log\Writer\Stream(BP . $file);
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->log($prior, $msg);
    }
}
