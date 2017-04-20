<?php

/**
 * @name SamplePlugin
 * @desc Yaf定义了如下的6个Hook,插件之间的执行顺序是先进先Call
 * @see http://www.php.net/manual/en/class.yaf-plugin-abstract.php
 * @author root
 */
use Yaf\Plugin_Abstract;
use Yaf\Request_Abstract;
use Yaf\Registry;
class LogPlugin extends Yaf\Plugin_Abstract
{
    protected $_log = null;
    public function __construct()
    {
        $logConfigObj = new Yaf\Config\Ini(APPLICATION_PATH_CONFIG . '/log.ini', APPLICATION_ENV);
        $logConfig = $logConfigObj->toArray();
        $logConfig = $logConfig['app'];
        $defaultType = isset($logConfig['logtype']) ? ucfirst(strtolower($logConfig['logtype'])) : 'Echolog';
        switch ($defaultType) {
            case 'Filelog' :
            case 'Echolog' :
            case 'Jsonlog':
                $className = "Api\\Log\\{$defaultType}";
                $this->_log = new $className($logConfig);
                break;
            default:
                $this->_log = new Api\Log\Echolog($logConfig);
        }
        $this->_log->registerErrorHandler();
        //注册log插件
        Registry::set('log', $this->_log);
    }

    public function routerStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }

    public function routerShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }

    public function dispatchLoopStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }

    public function preDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }

    public function postDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }

    public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
        $this->_log->flush();
    }

    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        echo $errno, $errstr;exit();
    }
}
