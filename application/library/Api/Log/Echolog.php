<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/27
 * Time: 14:34
 */
namespace Api\Log;
use Api;
class Echolog extends Api\Log implements Api\Iface\Log
{
    protected $_queue = array();
    protected $_isShell = false;
    protected $_display = true;
    public function __construct($config)
    {
        parent::__construct($config);
        $this->_isShell = isset($_SERVER['SHELL']);
    }

    public function put($msg, $level = Api\Log::NOTICE)
    {
        $date = '';
        $msgLog = $this->_format($msg, $level, $date);
        if ($msgLog) {
            if (!$this->_isShell) {
                $msgLog = str_replace("\n", '<br/>', $msgLog);
            }
            if ($this->_display) {
                echo $msgLog;
            }
        }
        return true;
    }

    public function setDisplay($flag)
    {
        $this->_display = $flag;
        return $this;
    }
    public function flush()
    {
    }
}