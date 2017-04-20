<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/28
 * Time: 11:18
 */
namespace Api\Service;
use Api\Globals\Defined;
use Syar\Protocol;
use Yaf\Dispatcher;
use \Yaf\Registry;
use \Hprose\Http\Server;
class Rpc
{
    protected $_rpcSerivce;
    protected $_currentResponse;
    const YAR = 'yarService';
    const HPROSE = 'hproseService';

    public function __construct()
    {
        $lang = Registry::get('_lang');
        switch ($lang) {
            case 'yar' :
                $this->_rpcService = self::YAR;
                break;
            default:
                $this->_rpcService = self::HPROSE;
                break;
        }
    }

    public function setResponse($response)
    {
        $this->_currentResponse = $response;
    }
    public function start($apiModel)
    {
        $authToken = \Yaf\Dispatcher::getInstance()->getRequest()->getServer('HTTP_AUTH_TOKEN', false);
        if (!$authToken) {
            $authToken = \Yaf\Dispatcher::getInstance()->getRequest()->getParam('auth-token', false);
        }
        $apiModel->authToken = $authToken;

        if (method_exists($apiModel, '__setParams')) {
            $apiModel->__setParams(\Yaf\Dispatcher::getInstance()->getRequest()->getParams());
        }
        call_user_func_array(array($this, $this->_rpcService), array($apiModel));
    }

    protected function yarService($apiModel)
    {
        $params = \Yaf\Dispatcher::getInstance()->getRequest()->getParams();
        $isSwoolePost = 0;
        if (\Yaf\Dispatcher::getInstance()->getRequest()->isPost()) {
            $str = end($params);
            if (empty($str) || empty(key($params))) {
                array_pop($params);
            }
            //判断
            if (defined('SWOOLE_SERVER')) {
                $isSwoolePost = 1;
            }
        }
        //test debug
//        $data = $apiModel->send();
//        var_dump(json_decode($data));
//        exit();
        //end
        if ($isSwoolePost) {
            //解析$sysProtocol['data']
            $protocol = new Protocol($apiModel);
            $response = new \Yaf\Response\Http();
            $d = Registry::has('systemServerProtocolRpcHeader') ?
                Registry::get('systemServerProtocolRpcHeader') : 0;
            $result = $protocol->onRequest(
                $d, \Yaf\Dispatcher::getInstance()->getRequest()->getMethod()
            );
            $response->setAllHeaders($result->getHeader());
            $response->setBody($result->getBody());
            return $response->response();
        } else {
            $yarHandler = new \Yar_Server($apiModel);
            return $yarHandler->handle();
        }
    }

    protected function hproseService($apiModel)
    {
        //反射类中的方法添加到接口里面
        //var_dump(\Yaf\Loader::getInstance()->getLibraryPath());
        \Yaf\Loader::import('Hprose/Autoload/Hprose.php');
        $refClass = new \ReflectionClass($apiModel);
        $methods = $refClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        $server = new Server();
        foreach ($methods as $k => $method) {
            if (strpos($method->name, '_') === 0) {
                unset($methods[$k]);
                continue;
            }
            $server->addFunction(array($apiModel, $method->name));
        }
        $server->start();
        //throw new \Exception ('暂不支持其他RPC方式');
    }
}