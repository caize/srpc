<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/27
 * Time: 10:24
 */
namespace Api;
abstract class Log
{
    const TRACE = 0;
    const INFO = 1;
    const NOTICE = 2;
    const WARN = 3;
    const ERROR = 4;
    protected static $_dateFormat = '[Y-m-d H:i:s]';
    protected static $_levelCodeMap = array(
        'TRACE' => self::TRACE,
        'INFO' => self::INFO,
        'NOTICE' => self::NOTICE,
        'WARN' => self::WARN,
        'ERROR' => self::WARN
    );

    protected static $_levelStrArr = array(
        self::TRACE => 'TRACE',
        self::INFO => 'INFO',
        self::NOTICE => 'NOTICE',
        self::WARN => 'WARN',
        self::ERROR => 'ERROR'
    );

    protected $_levelLine = null;
    protected $_config = null;
    protected $_isRegisterErrorHandler = false;
    public function __construct($config)
    {
        if (isset($config['level']) && isset(self::$_levelCodeMap[$config['level']])) {
            $this->setLevelLine(self::$_levelCodeMap[$config['level']]);
        } else {
            $this->setLevelLine(self::$_levelStrArr[self::NOTICE]);
        }
        $this->_config = $config;
    }
    
    public function registerTimer($time)
    {
        \Swoole\Timer::tick(
            $time,
            function () {
                $this->flush();
            }
        );
    }

    public function setLevelLine($level)
    {
        $this->_levelLine = $level;
        return $this;
    }

    protected function _format($msg, $level, &$date)
    {
        if ($level < $this->_levelLine) {
            return false;
        }
        if (is_array($msg)) {
            $msg = json_encode($msg);
        }
        $levelStr = self::$_levelStrArr[$level];
        $dateObj = new \DateTime();
        $date = $dateObj->format('Ymd');
        $log = $dateObj->format(self::$_dateFormat);
        return date($log) . "\t{$levelStr}\t{$msg}\n";
    }

    public function registerErrorHandler()
    {
        if ($this->_isRegisterErrorHandler) {
            return $this;
        }
        set_error_handler(array($this, 'errorHandler'));
        register_shutdown_function(
            function () {
                $e = error_get_last();
                if (empty($e)) {
                    return false;
                }
                $this->errorHandler($e['type'], $e['message'], $e['file'], $e['line']);
            }
        );
        $this->_isRegisterErrorHandler = true;
        return $this;
    }

    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext = '')
    {
        if (!(error_reporting() & $errno)) {
            return;
        }
        $mapStr = self::TRACE;;
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $mapStr = self::NOTICE;
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_USER_WARNING:
                $mapStr = self::WARN;
                break;
            case E_ERROR:
            case E_USER_ERROR:
            case E_CORE_ERROR:
            case E_RECOVERABLE_ERROR:
                $mapStr = self::ERROR;
                $str = sprintf("%s in %s on line %d", $errstr, $errfile, $errline);
                $this->put($str, $mapStr);
                $this->flush();
                exit(1);
                break;
            case E_STRICT:
            case E_DEPRECATED:
                $mapStr = self::INFO;
                break;
            default:
                $mapStr = self::TRACE;
        }
        $str = sprintf("%s in %s on line %d", $errstr, $errfile, $errline);
        $this->put($str, $mapStr);
    }
    abstract public function flush();

    public function __destruct()
    {
        $this->flush();
    }
}
