<?php
/**
 * 
 * @author lugang
 * 默认的自动加载类，根据模块自动加载插件
 */
class AutoloadPlugin extends Yaf\Plugin_Abstract
{
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
        echo '====';
    }
}