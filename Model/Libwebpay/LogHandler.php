<?php
namespace Transbank\Webpay\Model\Libwebpay;

/**
 * @author     Allware Ltda. (http://www.allware.cl)
 * @copyright  2018 Transbank S.A. (http://www.transbank.cl)
 * @date       May 2018
 * @license    GNU LGPL
 * @version    3.1.1
 */

use Transbank\Webpay\Model\Logger as Logger;

define('Webpay_ROOT', dirname(__DIR__));


class LogHandler
{
    private $timestamp;
    private $idTransaction;
    private $method;
    private $request;
    private $response;
    private $logFile;
    private $logDir;
    private $ecommerce;
    private $configuration;
    private $l4php;


    public function __construct($ecommerce = 'sdk', $days = 7, $weight = '2MB')
    {
        $this->timestamp = null;
        $this->idTransaction = null;
        $this->method = null;
        $this->request =null;
        $this->reponse = null;
        $this->logFile = null;
        $this->ecommerce = $ecommerce;
        $this->logDir = null;
        $this->lockfile = Webpay_ROOT."/set_logs_activate.lock";
        $dia = date('Y-m-d');
        $this->confdays = $days;
        $this->confweight = $weight;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directoryList = $objectManager->get('\Magento\Framework\App\Filesystem\DirectoryList');

        $this->path = $directoryList->getRoot();

        // define logdir en base a ecommerce
        switch ($this->ecommerce) {
      case 'prestashop':
        $this->logDir = _PS_ROOT_DIR_."/log/Transbank_webpay";
        break;
      case 'magento':
        $this->logDir = "/var/log/Transbank_webpay";
        break;
      case 'woocommerce':
        $this->logDir = ABSPATH."/log/Transbank_webpay";
        break;
      case 'opencart':
        $this->logDir = DIR_LOGS."/Transbank_webpay";
        break;
      case 'virtuemart':
        $root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
        include_once $root.'/configuration.php';
        $rot = new JConfig;//
        $this->logDir = $rot->log_path."/Transbank_webpay";
        break;
      case 'sdk':
        $this->logDir = Webpay_ROOT."/logs/Transbank_webpay";
        break;
      default:
    //    echo "error!: no se ha definido un directorio de logs correcto";
        exit;
        break;
    }

        $this->logFile = "{$this->logDir}/log_transbank_{$this->ecommerce}_{$dia}.log";
    }

    private function formatBytes($path)
    {
        $bytes = sprintf('%u', filesize($path));

        if ($bytes > 0) {
            $unit = intval(log($bytes, 1024));
            $units = array('B', 'KB', 'MB', 'GB');
            if (array_key_exists($unit, $units) === true) {
                return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
            }
        }

        return $bytes;
    }


    private function getIsLogDir()
    {
        if (! file_exists($this->logDir)) {
            //echo "error!: no existe directorio de logs, favor crear uno";
            return false;
        } else {
            return true;
        }
    }

    private function setMakeLogDir()
    {
        if ($this->getIsLogDir() === false) {

        } else {
            //   echo "error!: directorio ya ha sido creado";
            exit;
        }
    }

    private function setparamsconf($days, $weight)
    {
        if (file_exists($this->lockfile)) {
            $file = fopen($this->lockfile, "w") or die("No se puede truncar archivo");
            if (! is_numeric($days) or $days == null or $days == '' or $days === false) {
                $days = 7;
            }
            $txt = "{$days}\n";
            fwrite($file, $txt);
            $txt = "{$weight}\n";
            fwrite($file, $txt);
            fclose($file);
            // chmod($this->lockfile, 0600);
        } else {
            //  echo "error!: no se ha podido renovar configuracion";
            exit;
        }
    }

    private function setLockFile()
    {
        if (! file_exists($this->lockfile)) {
            $file = fopen($this->lockfile, "w") or die("No se puede crear archivo de bloqueo");
            if (! is_numeric($this->confdays) or $this->confdays == null or $this->confdays == '' or $this->confdays === false) {
                $this->confdays = $days;
            }
            $txt = "{$this->confdays}\n";
            fwrite($file, $txt);
            $txt = "{$this->confweight}\n";
            fwrite($file, $txt);
            fclose($file);
            // chmod($this->lockfile, 0600);
            return true;
        } else {
            // echo "Error!; archivo ya existe!";
            return false;
        }
    }

    public function getValidateLockFile()
    {
        if (! file_exists($this->lockfile)) {
            $result = array(
        'status' => false,
        'lock_file' => basename($this->lockfile),
        'max_logs_days' => '7',
        'max_log_weight' => '2'
      );
        } else {
            $lines = file($this->lockfile);
            $this->confdays = trim(preg_replace('/\s\s+/', ' ', $lines[0]));
            $this->confweight = trim(preg_replace('/\s\s+/', ' ', $lines[1]));
            $result = array(

        'status' => true,
        'lock_file' => basename($this->lockfile),
        'max_logs_days' => $this->confdays,
        'max_log_weight' => $this->confweight
      );
        }
        return $result;
    }
    private function delLockFile()
    {
        if (! file_exists($this->lockfile)) {
            // exit;
        } else {
            unlink($this->lockfile);
        }
    }

    private function setLogList()
    {
      if (! $this->path.$this->logDir) {
        $this->setMakeLogDir();
      }
        $arr = array_diff(scandir($this->path.$this->logDir), array('.', '..'));
        $dira = str_replace($_SERVER['DOCUMENT_ROOT'], "", $this->path.$this->logDir);
        foreach ($arr as $key => $value) {
            $var[] = "<a href='{$dira}/{$value}' download>{$value}</a>";
        }
        if (isset($var)) {
            $this->logList = $var;
        } else {
            $this->logList = null;
        }

        return $this->logList;
    }



    public function setTransactionId($token)
    {
        $this->transactionID = $token;
    }

    private function getTransactionId()
    {
        return $this->transactionID;
    }


    private function setLastLog()
    {
        $files = glob($this->logDir."/*.log");
        if (!$files) {
            return array("No existen Logs disponibles");
        }
        $files = array_combine($files, array_map("filemtime", $files));
        arsort($files);
        $this->lastLog = key($files);
        if (isset($this->lastLog)) {
            $var = file_get_contents($this->lastLog);
        } else {
            $var = null;
        }
        $return = array(
      'log_file' => basename($this->lastLog),
      'log_weight' => $this->formatBytes($this->lastLog),
      'log_regs_lines' => count(file($this->lastLog)),
      'log_content' => $var
    );
        return $return;
    }

    private function readLogByFile($filename)
    {
        $var = file_get_contents($this->logDir."/".$filename);
        $return = array(
     'log_file' => $filename,
     'log_content' => $var
   );
        return $return;
    }

    private function setCountLogByFile($filename)
    {
        $fp = file($this->logDir."/".$filename);
        $return  = array(
      'log_file' => $filename,
      'lines_regs' => count($fp)
    );
        return $return;
    }

    private function setLastLogCountLines()
    {
        $lastfile = $this->setLastLog();

        $fp = file($this->logDir."/".$lastfile['log_file']);
        $return  = array(
      'log_file' => basename($lastfile['log_file']),
      'lines_regs' => count($fp)
    );
        return $return;
    }

    private function setLogNewLine($args, $type)
    {

        // $this->digestLogs();
        $content =  "[{$args['transactionId']}] [{$args['method']}] [{$args['request']}] [{$args['response']}] ";
        if ($type === true) {
            Logger::log($content, $this->logFile, 'info');
        } elseif ($type === false) {
            Logger::log($content, $this->logFile, 'error');
        } else {
            Logger::log('se ha ingresado parametro no valido en la creacion de log', $this->logFile, 'warn');
        }
    }

    private function setLogDir()
    {
        return $this->logDir;
    }

    private function setLogCount()
    {
        $count = count($this->setLogList());
        $result = array('log_count' => $count);
        return $result;
    }


    /** Funciones de mantencion de directorio de logs**/

    // limpieza total de directorio

    private function delAllLogs()
    {
        if (! file_exists($this->logDir)) {
            // echo "error!: no existe directorio de logs";
            exit;
        }
        $files = glob($this->logDir.'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }

    // mantiene solo los ultimos n dias de logs
    private function digestLogs()
    {
        if (! file_exists($this->logDir)) {
            // echo "error!: no existe directorio de logs";
            $this->setMakeLogDir();
            //exit;
        }
        $files = glob($this->logDir.'/*', GLOB_ONLYDIR);
        $deletions = array_slice($files, 0, count($files) - $this->confdays);
        foreach ($deletions as $to_delete) {
            array_map('unlink', glob("$to_delete"));
            //$deleted = rmdir($to_delete);
        }

        return true;
    }




    /**Funciones de retorno**/

    // Obtiene archivo de bloqueo
    public function getLockFile()
    {
        return json_encode($this->getValidateLockFile());
    }
    // obtiene directorio de log
    public function getLogDir()
    {
        return json_encode($this->setLogDir());
    }
    // obtiene conteo de logs en logdir definido
    public function getLogCount()
    {
        return json_encode($this->setLogCount());
    }
    // obtiene listado de logs en logdir
    public function getLogList()
    {
        return json_encode($this->setLogList());
    }
    // obtiene ultimo log modificado (al crearse con timestamp es tambien el ultimo creado)
    public function getLastLog()
    {
        return json_encode($this->setLastLog());
    }
    // obtiene conteo de lineas de ultimo log creado
    public function getLastLogCountLines()
    {
        return json_encode($this->setLastLogCountLines());
    }
    // obtiene log en base a parametro
    public function getLogByFile($filename)
    {
        return json_encode($this->readLogByFile($filename));
    }

    // obtiene conteo de lineas de log en base a parametro
    public function getCountLogByFile($filename)
    {
        return json_encode($this->setCountLogByFile($filename));
    }


    // escribe en log
    public function writeLog($method, $id = null, $request, $response = null, $info = true)
    {
        $status = $this->getValidateLockFile();
        if ($method == 'initTransaction') {
            $cookie_value = (string)$id;
            setcookie('buyorder', $cookie_value, time() + 30, "/"); // 86400 = 1 day
        } elseif ($method == 'acknowledgeTransaction' and isset($_COOKIE['buyorder'])) {
            $id = $_COOKIE['buyorder'];
        }
        $args = array(
    'method' => $method,
    'transactionId' => (string)$id,
    'request' => json_encode($request),
    'response' => json_encode($response)  );
        if ($status['status'] === true) {
            $this->setLogNewLine($args, $info);
        }
    }

    public function delLogsFromDir()
    {
        $this->delAllLogs();
    }

    public function delKeepOnlyLastLogs()
    {
        $this->digestLogs();
    }


    public function setLockStatus($status = true)
    {
        if ($status === true) {
            $this->setLockFile();
        } else {
            $this->delLockFile();
        }
    }


    public function getResume()
    {
        $result = array(
      'config' => $this->getValidateLockFile(),
      'log_dir' => $this->setLogDir(),
      'logs_count' => $this->setLogCount(),
      'logs_list' => $this->setLogList(),
      'last_log' => $this->setLastLog(),
    );
        return json_encode($result, JSON_PRETTY_PRINT); // NOTE: eliminar el pretty print antes de pasar a produccion
    }

    public function setnewconfig($days, $weight)
    {
        $this->setparamsconf($days, $weight);
    }
}
