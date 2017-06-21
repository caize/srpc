<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 10:05
 */
namespace Api\Globals;
use Api\Request\Http;
use Yaf\Registry;
class Functions
{
    public static function lockProcess()
    {
        static $lockFileHandle;
        $phpSelf = realpath($_SERVER['PHP_SELF']);
        $lockFile = $phpSelf . '.lock';
        $lockFileHandle = fopen($lockFile, 'w');
        if ($lockFileHandle === false)
            die("Can not create lock file $lockFile\n");
        if (!flock($lockFileHandle, LOCK_EX + LOCK_NB)) {
            die(date("Y-m-d H:i:s") . "Process already exists.\n");
        }
    }

    public static function getCacheExpire($expire, $default = 1800)
    {
        if ($expire > 0 ) {
            return  $expire;
        }
        return $default;
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
    public static function swooleHttpWriteLog(\Api\Iface\Log $log, \Api\Request\Http $request, $serverType, $runTime)
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
                $appid = $cacheManager->get($redisConfig['api']['token'] . $params['auth-token']);
                if (!empty($appid)) {
                    $tokenMap[$params['auth-token']] = $appid;
                }
            }
            unset($params['auth-token']);
        } elseif (isset($params['@appid'])) {
            $appid = $params['@appid'];
            unset($params['@appid']);
        }
        //过滤掉secret
        if (isset($params['secret']))
            unset($params['secret']);
        $arr = array(
            'time' => $runTime,
            'type' => $serverType,
            'url' => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'params' => http_build_query($params),
            'appid' => $appid,
            'remoteIp' => Functions::getIpAddress(),
        );
        $log->put($arr);
    }

    public static function getIpAddress()
    {
        $ipAddress = '';
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $ipAddress = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $ipAddress = $_SERVER["HTTP_CLIENT_IP"];
            } elseif (isset($_SERVER["REMOTE_ADDR"])) {
                $ipAddress = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $ipAddress = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                $ipAddress = getenv("HTTP_CLIENT_IP");
            } else {
                $ipAddress = getenv("REMOTE_ADDR");
            }
        }
        return $ipAddress;
    }

    public static function getServerName()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            return $_SERVER['HTTP_X_FORWARDED_HOST'];
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }
        return '';
    }

    public static function getHttpProto()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'
            || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
        ) {
            return 'https://';
        }
        return 'http://';
    }

    public static function cookieToString($cookieArr)
    {
        if (is_array($cookieArr)) {
            $tCookieArr = [];
            foreach ($cookieArr as $k => $v) {
                $tCookieArr[] = "{$k}={$v}";
            }
            return implode('; ', $tCookieArr);
        }
        return $cookieArr;
    }
}
