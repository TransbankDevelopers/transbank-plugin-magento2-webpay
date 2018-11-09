<?php

namespace Transbank\Webpay\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Transbank\Webpay\Model\Libwebpay\HealthCheck;
use Transbank\Webpay\Model\Libwebpay\LogHandler;
use Magento\App\Framework\Route\Config;


class TbkButton extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Transbank_Webpay::system/config/button.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [] ) {
        parent::__construct($context, $data);
        $this->ecommerce = 'magento';
        $this->context = $context;

        $this->args = array(
              "ECOMMERCE" => $this->ecommerce,
              "MODO" =>  $this->_scopeConfig->getValue('payment/webpay/security_parameters/environment'),
              "PRIVATE_KEY" =>  $this->_scopeConfig->getValue('payment/webpay/security_parameters/private_key'),
              "PUBLIC_CERT" =>  $this->_scopeConfig->getValue('payment/webpay/security_parameters/public_cert'),
              "WEBPAY_CERT" =>  $this->_scopeConfig->getValue('payment/webpay/security_parameters/webpay_cert'),
              "COMMERCE_CODE" =>  $this->_scopeConfig->getValue('payment/webpay/security_parameters/commerce_code')
          );
        $this->logH = new loghandler($this->args['ECOMMERCE']);
        $this->healthcheck = new HealthCheck($this->args);
      	$datos_hc = json_decode($this->healthcheck->printFullResume());
        $this->tbk_data = array(
              'cert_vs_private' =>$datos_hc->validate_certificates->consistency->cert_vs_private_key,
        			'commerce_code_validate' => $datos_hc->validate_certificates->consistency->commerce_code_validate,
        			'subject_commerce_code' => $datos_hc->validate_certificates->cert_info->subject_commerce_code,
        			'cert_version' => $datos_hc->validate_certificates->cert_info->version,
        			'cert_is_valid' => $datos_hc->validate_certificates->cert_info->is_valid,
        			'valid_from' => $datos_hc->validate_certificates->cert_info->valid_from,
        			'valid_to' => $datos_hc->validate_certificates->cert_info->valid_to,
        			'init_status' => null, //$datos_hc->validate_init_transaction->status->string,
        			'init_error_error' => null, // (isset($datos_hc->validate_init_transaction->response->error)) ? $datos_hc->validate_init_transaction->response->error : NULL,
        			'init_error_detail' => null, // (isset($datos_hc->validate_init_transaction->response->detail)) ? $datos_hc->validate_init_transaction->response->detail : NULL,
        			'init_success_url' => null, // (isset($datos_hc->validate_init_transaction->response->url)) ? $datos_hc->validate_init_transaction->response->url : NULL,
        			'init_success_token' => null, //  (isset($datos_hc->validate_init_transaction->response->token_ws)) ? $datos_hc->validate_init_transaction->response->token_ws : NULL,
        			'php_status' =>$datos_hc->server_resume->php_version->status,
        			'php_version' =>$datos_hc->server_resume->php_version->version,
        			'server_version' =>$datos_hc->server_resume->server_version->server_software,
        			'ecommerce' =>$datos_hc->server_resume->plugin_info->ecommerce,
        			'ecommerce_version' =>$datos_hc->server_resume->plugin_info->ecommerce_version,
        			'current_plugin_version' =>$datos_hc->server_resume->plugin_info->current_plugin_version,
        			'last_plugin_version' =>$datos_hc->server_resume->plugin_info->last_plugin_version,
        			'openssl_status' =>$datos_hc->php_extensions_status->openssl->status,
        			'openssl_version' =>$datos_hc->php_extensions_status->openssl->version,
        			'SimpleXML_status' =>$datos_hc->php_extensions_status->SimpleXML->status,
        			'SimpleXML_version' =>$datos_hc->php_extensions_status->SimpleXML->version,
        			'soap_status' =>$datos_hc->php_extensions_status->soap->status,
        			'soap_version' =>$datos_hc->php_extensions_status->soap->version,
        			'mcrypt_status' =>$datos_hc->php_extensions_status->mcrypt->status,
        			'mcrypt_version' =>$datos_hc->php_extensions_status->mcrypt->version,
        			'dom_status' =>$datos_hc->php_extensions_status->dom->status,
        			'dom_version' =>$datos_hc->php_extensions_status->dom->version,
        			'php_info' =>$datos_hc->php_info->string->content,
        		  'lockfile' => json_decode($this->logH->getLockFile(),true)['status'],
        			'logs' => (isset( json_decode($this->logH->getLastLog(),true)['log_content'])) ?  json_decode($this->logH->getLastLog(),true)['log_content'] : NULL,
        			'log_file' => (isset( json_decode($this->logH->getLastLog(),true)['log_file'])) ?  json_decode($this->logH->getLastLog(),true)['log_file'] : NULL,
        			'log_weight' => (isset( json_decode($this->logH->getLastLog(),true)['log_weight'])) ?  json_decode($this->logH->getLastLog(),true)['log_weight'] : NULL,
        			'log_regs_lines' => (isset( json_decode($this->logH->getLastLog(),true)['log_regs_lines'])) ?  json_decode($this->logH->getLastLog(),true)['log_regs_lines'] : NULL,
        			'log_days' => $this->logH->getValidateLockFile()['max_logs_days'],
        			'log_size' => $this->logH->getValidateLockFile()['max_log_weight'],
        			'log_dir' => json_decode($this->logH->getResume(),true)['log_dir'],
        			'logs_count' => json_decode($this->logH->getResume(),true)['logs_count']['log_count'],
        			'logs_list' => (isset(json_decode($this->logH->getResume(),true)['logs_list'])) ?json_decode($this->logH->getResume(),true)['logs_list'] : array('no hay archivos de registro') ,
            );
    }


    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
?>
