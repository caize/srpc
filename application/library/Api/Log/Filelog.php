<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/27
 * Time: 14:34
 */
namespace Api\Log;
use Api;
class Filelog extends Api\Log implements Api\Iface\Log
{
    protected $_logPath;
    protected $_logFIle;
    protected $_enabledCache;
    protected $_logExt = 'log';
    protected $_queue = array();
    protected $_cacheNum = 10;
    protected $_logDateCurrent = '';
    /**
     * @var fopen resource
     */
    protected $_fHandle = null;
    public function __construct($config)
    {
        parent::__construct($config);
        if (!isset($config['path'])) {
            throw new \Exception('filelog config  can not find path config');
        }
        $this->_logPath = rtrim($config['path'], '/');
        $this->_enabledCache = isset($config['enabledCache']) && $config['enabledCache'];
        $this->_cacheNum = isset($config['cacheNum']) && is_numeric($config['cacheNum']) ? $config['cacheNum'] : 10;
    }

    public function put($msg, $level = Api\Log::NOTICE)
    {
        $date = '';
        $msgLog = $this->_format($msg, $level, $date);
        if (!isset($this->_queue[$date])) {
            $this->_queue[$date] = array();
        }
        if (empty($msgLog)) {
            return false;
        }
        $this->_queue[$date][] = $msgLog;
        if (count($this->_queue, COUNT_RECURSIVE) > $this->_cacheNum || !$this->_enabledCache) {
            $this->flush();
        }
        return true;
    }

    public function flush()
    {
        if (empty($this->_queue)) {
            return false;
        }
        foreach ($this->_queue as $date => $items) {
            if ($date != $this->_logDateCurrent) {
                $this->_closeFile();
                $this->_logFIle = $this->_logPath . '/' . $date . '.' . 'log';
                $this->_logDateCurrent = $date;
                $this->_fHandle = $this->_openFile($this->_logFIle);
            }
            fwrite($this->_fHandle, implode('', $items));
        }
        $this->_queue = array();
    }

    protected function _openFile($file)
    {
        if (!file_exists($file) && touch($file)) {
            $old = umask(0);
            chmod($file, 0777);
            umask($old);
        }
        $fp = fopen($file, 'a+');
        if (!$fp) {
            throw new \Exception(__CLASS__ . ': can not open file logfile(' . $file . ') line:' . __LINE__);
        }
        return $fp;
    }

    protected function _closeFile()
    {
        if (null != $this->_fHandle) {
            fclose($this->_fHandle);
        }
    }
}