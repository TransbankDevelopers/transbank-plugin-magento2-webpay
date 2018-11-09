<?php
namespace Transbank\Webpay\Model\Libwebpay\tcpdf;
use Transbank\Webpay\Model\Libwebpay\tcpdf\reportPDF;
use Transbank\Webpay\Model\Libwebpay\LogHandler;

	class reportPDFlog{
		private $ecommerce;

		function __construct($ecommerce, $document){
			$this->ecommerce = $ecommerce;
			$this->document = $document;
		}
		function getReport($myJSON){
			$log = new LogHandler($this->ecommerce);
			$json = json_decode($log->getLastLog(),true);
					 $obj = json_decode($myJSON,true);
					 if (isset($json['log_content']) && $this->document == 'report'){
						 $html = str_replace("\r\n","<br>",$json['log_content']);
						 $html = str_replace("\n","<br>",$json['log_content']);
							$text = explode ("<br>" ,$html);
							$html='';
							foreach ($text as $row){
		 					$html .= '<b>'.substr($row,0,21).'</b> '.substr($row,22).'<br>';
							}
							$obj += array('logs' => array('log' => $html));
						}
				$html = '';

				$report = new reportPDF();
				$report->getReport(json_encode($obj));
			}

		}
