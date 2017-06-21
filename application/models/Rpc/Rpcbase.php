<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/5
 * Time: 16:21
 */
namespace Rpc;


use Api\Globals\Functions;
use \Yaf\Registry;
class RpcbaseModel
{
    protected $_params = array();
    protected $_authToken = null;
    protected $_appid = null;
    protected $_startTime = null;
    public function __construct()
    {
        $this->_startTime = microtime(true);
    }

    public function __set($name, $value)
    {
        $v = '_' . $name;
        $this->$v = $value;
    }

    protected function _foundAppidFromParams($params)
    {
        if (isset($params['@appid'])) {
            $this->_appid = $params['@appid'];
            unset($params['@appid']);
        }
        return $params;
    }

    protected function _getCacheData($cacheKey)
    {
        return Registry::get('cacheManager')->get($cacheKey);
    }

    protected function _setCacheData($cacheKey, $data, $expire)
    {
        Registry::get('cacheManager')->set($cacheKey, $data);
        Registry::get('cacheManager')->expire($cacheKey, Functions::getCacheExpire($expire));
    }
    protected function _encryptCacheKey($params)
    {
        ksort($params);
        $className = strtolower(get_class($this));
        $className = preg_replace('/\\\\|_/', '', $className);
        return 'rpcdata_' . $className . '_' . sha1(implode("", $params));
    }

    protected function _errorCallback($return, $params, $routerMap)
    {
        //计入日志
        $logArr = array(
            'code' => $return['errorcode'],
            'msg' => $return['errormsg'],
            'url' => $routerMap['router'],
            'serviceName' => $routerMap['name'],
            'serviceUrl' => $routerMap['url'],
            'params' => $params,
            'appid' => $this->_appid
            //'serviceHost' => $routerMap['host'],
        );
        if (isset($return['traces'])) {
            $logArr['traces'] = $return['traces'];
        }
        if (Registry::has('log')) {
            Registry::get('log')->put($logArr, \Api\Log::ERROR);
        }
    }

    protected function _disabledMethod()
    {
        return json_encode(
            [
                'errorcode' => \Api\Globals\Defined::CODE_API_METHOD_DISABLED,
                'errormsg' => \Api\Globals\Defined::MSG_API_METHOD_DISABLED
            ]
        );
    }
}