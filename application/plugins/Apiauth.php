<?php
/**
 *
 * @author lugang
 * api 认证接口 遵循RPC原理，只当post请求时进行验证
 */
use Illuminate\Database\Capsule\Manager as DB;
use Yaf\Exception;
use Yaf\Registry;

class ApiauthPlugin extends Yaf\Plugin_Abstract
{
    public function routerStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }

    public function routerShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
        $routerStr = strtolower($request->getModuleName()
            . '_' . $request->getControllerName()
            . '_' . $request->getActionName());

        $redisConfig = Registry::get('redisConfig');
        $cacheObj = Registry::get('cacheManager');
        $cacheObj->setProjectKey('api');
        $cacheRoutMaper = $cacheObj->get($redisConfig['api']['routermap'] . $routerStr);
        //throw new Yaf\Exception('auth faild', -10000);
        if (empty($cacheRoutMaper)) {
            throw new Exception('auth faild', -10000);
        }
        $cacheRoutMaper = json_decode($cacheRoutMaper, true);
        if ($cacheRoutMaper && !$cacheRoutMaper['isauth']) {
            Registry::set('routerMapData', $cacheRoutMaper);
            return true;
        }
        //get appid
        $authToken = $request->getServer('HTTP_AUTH_TOKEN', false);
        if (!$authToken) {
            $authToken = $request->getParam('auth-token', false);
        }
        if (!$authToken) {
            throw new Exception('auth faild, token not found!', -10001);
        }
        $appid = $cacheObj->get($redisConfig['api']['token'] . sha1($authToken));

        if (!$appid) {
            throw new Exception('auth faild, appid not found!', -10002);
        }

        $authResource = $cacheObj->hget($redisConfig['api']['authresoucre'] . $appid, $cacheRoutMaper['id']);
        if (!$authResource) {
            throw new \Exception('auth filed', -10004);
        }
        Registry::set('routerMapData', $cacheRoutMaper);
        return true;
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
    }
}