<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/5
 * Time: 16:21
 */
namespace Rpc;

use Yaf\Registry;
use Api\Globals\Defined;

class RpcauthModel
{
    protected static $_redisConfig = null;
    protected static $_cacheManager = null;

    public static function getRedisConfig()
    {
        if (self::$_redisConfig === null) {
            self::$_redisConfig = Registry::get('redisConfig');
        }
        return self::$_redisConfig;
    }

    public static function getCacheManager()
    {
        if (self::$_cacheManager === null) {
            self::$_cacheManager = Registry::get('cacheManager');
        }
        return self::$_cacheManager;
    }

    public static function getRouterMap($token = null)
    {
        $request = \Yaf\Dispatcher::getInstance()->getRequest();
        $routerStr = strtolower(
            $request->getModuleName()
            . '_' . $request->getControllerName()
            . '_' . $request->getActionName()
        );

        $redisConfig = self::getRedisConfig();
        $cacheObj = self::getCacheManager();
        $cacheRoutMaper = $cacheObj->get($redisConfig['api']['routermap'] . $routerStr);
        if (empty($cacheRoutMaper)) {
            throw new \Exception(Defined::MSG_API_AUTH_FAILED_NOT_MAP, Defined::CODE_API_AUTH_FAILED_NOT_MAP);
        }
        $cacheRoutMaper = json_decode($cacheRoutMaper, true);
        if ($cacheRoutMaper && !$cacheRoutMaper['isauth']) {
            return $cacheRoutMaper;
        }
        $authToken = $token;
        if (!$authToken) {
            throw new \Exception(
                Defined::MSG_API_AUTH_FAILED_NOT_FOUND_TOKEN,
                Defined::CODE_API_AUTH_FAILED_NOT_FOUND_TOKEN
            );
        }
        $appid = $cacheObj->get($redisConfig['api']['token'] . $authToken);
        if (!$appid) {
            throw new \Exception(
                Defined::MSG_API_AUTH_FAILED_NOT_FOUND_TOKEN,
                Defined::CODE_API_AUTH_FAILED_NOT_FOUND_APPID
            );
        }

        $authResource = $cacheObj->hget($redisConfig['api']['authresoucre'] . $appid, $cacheRoutMaper['id']);
        if (!$authResource) {
            throw new \Exception(
                Defined::MSG_API_AUTH_FAILED_NOT_FOUND_RESOURCE,
                Defined::CODE_API_AUTH_FAILED_NOT_FOUND_RESOURCE
            );
        }
        $cacheRoutMaper['appid'] = $appid;
        return $cacheRoutMaper;
    }

    public static function getThirdAuthInfo($appid, $type, $thirdHost)
    {
        $cache = null;
        if (in_array($type, Defined::getOtherAuthArray())) {
            $redisConfig = self::getRedisConfig();
            $cache = self::getCacheManager()->hGet($redisConfig['api']['auththirdbind'] . $appid, $type);
            if (!$cache) {
                $cron = new \Externalapi\Cron\UpdateServiceCacheDataModel();
                $cacheArr = $cron->setThirdAuth($appid);
                if (!empty($cacheArr) && isset($cacheArr[$appid][$type])) {
                    $cache = $cacheArr[$appid][$type];
                }
            }
        }
        if (empty($cache)) {
            throw new \Exception(
                Defined::MSG_API_AUTH_FAILED_THIRD_NOT_FOUND,
                Defined::CODE_API_AUTH_FAILED_THIRD_NOT_FOUND
            );
        }
        $info = array();
        $cacheArr = json_decode($cache, true);
        switch ($type) {
            case Defined::OTHER_AUTH_IWENCAI:
                $tokenObj = new \Auth\TokenIwencaiModel();
                $result = $tokenObj->getToken($cacheArr['third_name'], $cacheArr['third_pwd'], $thirdHost);
                if ($result->isValid()) {
                    $data = $result->getResultData();
                    $info['header']['Access-Token'] = $data['token'];
                } else {
                    throw new \Exception(
                        $result->getResultMsg(),
                        $result->getResultCode()
                    );
                }
                break;
        }
        return $info;
    }
}