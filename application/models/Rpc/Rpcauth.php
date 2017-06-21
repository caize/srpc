<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/5
 * Time: 16:21
 */
namespace Rpc;

use Api\Globals\Functions;
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


    public static function getRouterMap($token = null, $appid = null)
    {
        $cacheRoutMaper = self::getRouterMapInfo();
        $stdClass = new \stdClass();
        $stdClass->token = $token;
        $stdClass->appid = $appid;
        $stdClass->routerMap = $cacheRoutMaper;
        $cacheRoutMaper = self::checkRouter($stdClass);
        return $cacheRoutMaper;
    }


    public static function getRouterMapInfo()
    {
        $request = \Yaf\Dispatcher::getInstance()->getRequest();
        $routerStr = strtolower(
            $request->getModuleName()
            . '_' . $request->getControllerName()
            . '_' . $request->getActionName()
        );
        try {
            $redisConfig = self::getRedisConfig();
            $cacheObj = self::getCacheManager();
            $cacheRoutMaper = $cacheObj->get($redisConfig['api']['routermap'] . $routerStr);
            if (empty($cacheRoutMaper)) {
                throw new \Exception(Defined::MSG_API_AUTH_FAILED_NOT_MAP, Defined::CODE_API_AUTH_FAILED_NOT_MAP);
            }
            $cacheRoutMaper = json_decode($cacheRoutMaper, true);
        } catch (\Exception $e) {
            $routerModel = new \Datawarehouse\Serviceapi\RouterModel();
            $routerMap = $routerModel->getRouterMap(str_replace('_', '/', $routerStr));
            if ($routerMap) {
                $cacheRoutMaper = (array)$routerMap;
            } else {
                throw new \Exception(Defined::MSG_API_AUTH_FAILED_NOT_MAP, Defined::CODE_API_AUTH_FAILED_NOT_MAP);
            }
        }
        if (isset($cacheRoutMaper['status']) && $cacheRoutMaper['status'] == 0) {
            throw new \Exception(
                Defined::MSG_API_AUTH_FAILED_SERVICE_OFFLINE, Defined::CODE_API_AUTH_FAILED_SERVICE_OFFLINE
            );
        }
        return $cacheRoutMaper;
    }

    public static function checkAppid($appid)
    {
        if (
            $appid === null ||
            \Yaf\Registry::has('swooleTable') &&
            \Yaf\Registry::get('swooleTable') instanceof \Swoole\Table &&
            !\Yaf\Registry::get('swooleTable')->exist($appid)
        ) {
            /**
             * 20170616 强制验证appid，用于统计
             */
            throw new \Exception(
                Defined::MSG_API_AUTH_FAILED_NOT_FOUND_APPID . '! 请联系l.gang06@yahoo.com申请',
                Defined::CODE_API_AUTH_FAILED_NOT_FOUND_APPID
            );
        }
        return true;
    }
    public static function checkRouter($class)
    {
        $cacheRoutMaper = $class->routerMap;
        if ($cacheRoutMaper && !$cacheRoutMaper['isauth']) {
            self::checkAppid($class->appid);
            return $cacheRoutMaper;
        }
        $cacheObj = self::getCacheManager();
        $redisConfig = self::getRedisConfig();
        if (!empty($class->appid)) {
            if (!self::checkIpTables($class->appid)) {
                throw new \Exception(
                    Defined::MSG_API_AUTH_FAILED_IPTABLE_CHECK,
                    Defined::CODE_API_AUTH_FAILED_IPTABLE_CHECK
                );
            }
            $cacheRoutMaper['appid'] = $class->appid;
            return $cacheRoutMaper;
        } else {
            $authToken = $class->token;
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
        }
        self::checkAppid($cacheRoutMaper['appid']);
        return $cacheRoutMaper;
    }

    public static function checkIpTables($appid)
    {
        $clientAddr = Functions::getIpAddress();
        $cacheData = self::getThirdAuthRedisCache($appid, Defined::OTHER_AUTH_LOCAL);
        if (empty($cacheData)) {
            return false;
        }
        $cacheData = json_decode($cacheData, true);
        if (!is_array($cacheData)) {
            return false;
        }
        $cacheData = array_flip($cacheData);
        if (isset($cacheData[$clientAddr])) {
            return true;
        }
        return false;
    }

    public static function getThirdAuthRedisCache($appid, $type)
    {
        $redisConfig = self::getRedisConfig();
        $cache = self::getCacheManager()->hGet($redisConfig['api']['auththirdbind'] . $appid, $type);
        return $cache;

    }
    public static function getThirdAuthInfo($appid, $type, $thirdHost = null)
    {
        $cache = null;
        if (in_array($type, Defined::getOtherAuthArray())) {
            $cache = self::getThirdAuthRedisCache($appid, $type);
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
                $info['header']['Access-Token'] = self::iwencaiApiToken(
                    $cacheArr['third_name'], $cacheArr['third_pwd'], $thirdHost
                );
                break;
        }
        return $info;
    }

    public static function iwencaiApiToken($appid, $secret, $host)
    {
        $tokenObj = new \Auth\TokenIwencaiModel();
        $result = $tokenObj->getToken($appid, $secret, $host);
        if (!$result->isValid()) {
            throw new \Exception(
                $result->getResultMsg(),
                $result->getResultCode()
            );
        }
        $data = $result->getResultData();
        return $data['token'];
    }

    /**
     * @param $appid 应用ID
     * @param $authFrom 认证来源  Defined::getOtherAuthArray() + Defined::OTHER_AUTH_DEFAULT
     *  检查访问白名单
     */
    public static function checkIp($appid, $authFrom)
    {

    }
}