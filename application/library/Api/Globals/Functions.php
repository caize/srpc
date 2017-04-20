<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 10:05
 */
namespace Api\Globals;
class Functions
{
    public static function lockProcess()
    {
        $phpSelf = realpath($_SERVER['PHP_SELF']);
        $lockFile = $phpSelf . '.lock';
        $lockFileHandle = fopen($lockFile, 'w');
        if ($lockFileHandle === false)
            die("Can not create lock file $lockFile\n");
        if (!flock($lockFileHandle, LOCK_EX + LOCK_NB)) {
            die(date("Y-m-d H:i:s") . "Process already exists.\n");
        }
    }

    public static function apiParamsCheck($params, $mergeArr = array())
    {
        if (is_array($params)) {
            if (!empty($mergeArr)) {
                return array_merge($params, $mergeArr);
            }
            return $params;
        } else {
            $json = json_decode($params, true);
            if (empty($json))
                return $mergeArr;
            elseif (is_array($json))
                return array_merge($json, $mergeArr);
            return $json;
        }
    }

    public static function iconvArr($inCharset, $outCharset, $arr)
    {
        return eval('return ' . iconv($inCharset, $outCharset, var_export($arr, true)) . ';');
    }

    /**
     * @param \Api\Iface\Log $log
     * @param \Yaf\Request_Abstract $request
     * @param $serverType  http|tcp
     */
    public static function swooleHttpWriteLog(\Api\Iface\Log $log, \Yaf\Request_Abstract $request, $serverType)
    {
        static $tokenMap = array();
        $params = $request->getParams();
        $appid = null;
        if (isset($params['auth-token'])) {
            if (isset($tokenMap[$params['auth-token']])) {
                $appid = $tokenMap[$params['auth-token']];
            } else if (!empty($params['auth-token'])) {
                //she cache
                $cacheManager = Registry::get('cacheManager');
                $redisConfig = Registry::get('redisConfig');
                $appid = $cacheManager->set($redisConfig['api']['token'] . $params['auth-token']);
                if (!empty($appid)) {
                    $tokenMap[$params['auth-token']] = $appid;
                }
            }
            unset($params['auth-token']);
        }
        //过滤掉secret
        if (isset($params['secret']))
            unset($params['secret']);
        $arr = array(
            'type' => $serverType,
            'url' => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'params' => http_build_query($params),
            'appid' => $appid
        );
        $log->put($arr);
    }
}
