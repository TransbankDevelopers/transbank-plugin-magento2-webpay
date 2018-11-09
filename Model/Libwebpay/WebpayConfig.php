<?php
/**
 * @author     Allware Ltda. (http://www.allware.cl)
 * @copyright  2018 Transbank S.A. (http://www.transbank.cl)
 * @date       May 2018
 * @license    GNU LGPL
 * @version    3.1.1
 */
namespace Transbank\Webpay\Model\Libwebpay;

class WebpayConfig{
	private $params = array();

	function __construct($params){
		$this->params = $params;
	}

	public function getParams(){
		return $this->params;
	}

	public function getParam($name){
		return $this->params[$name];
	}

	public function getModo(){
		$modo = $this->params["MODO"];
		if (!isset($modo) || $modo == "")
		{
			$modo = "INTEGRACION";
		}
		return $modo;
	}
}
?>
