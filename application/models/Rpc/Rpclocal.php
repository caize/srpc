<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/5
 * Time: 16:21
 */
namespace Rpc;
use Yaf\Registry;
use Common\Result\HttpModel as ResultModel;
class RpclocalModel extends RpcbaseModel
{
    protected function _getLogParams($params)
    {
        $newParams = [];
        $newParams['@appid'] = $this->_appid;
        foreach ($params as $k => $val) {
            if (is_array($val)) {
                return $this->_getLogParams($val);
            }
            $newParams[$k] = mb_substr($val, 0, 10);
            if (strlen($val) > 10) {
                $newParams[$k] .=  '...';
            }
        }
        return $newParams;
    }
    /**
     * @param array $params
     *     对应http请求的参数
     *     有两种方式进行交互
     *      1. 直接绑定在请求url中如 http://api.10jqka.com.cn/api/test/test?test=1,
     *          这种方式 $params可以不穿
     *      2. 调用方法时传入，如 http://api.10jqka.com.cn/api/test/test,
     *          指定call方法时传入 array('test' => 1);
     *      注:如果同时传入http参数和call中的params参数，相同的key以params参数为准
     * @return json|xml
     */
    protected function _send($callBack, $params = array())
    {
        $result = new ResultModel();
        $startTime = microtime(true);
        $params = $this->_foundAppidFromParams($params);
        $logParams = $this->_getLogParams($params);
        try {
            $cached = isset($params['@cached']) ? intval($params['@cached']) : -1;
            $encryptCache = false;
            if ($cached >= 0) {
                unset($params['@cached']);
                $cacheKey = $this->_encryptCacheKey($params);
                $encryptCache = $this->_getCacheData($cacheKey);

            }
            if (false !== $encryptCache) {
                $result->setResultData(json_decode($encryptCache, true));
            } else {
                $routerMap = RpcauthModel::getRouterMapInfo();
                $stdClass = new \stdClass();
                $stdClass->token = $this->_authToken;
                $stdClass->appid = $this->_appid;
                $stdClass->routerMap = $routerMap;
                $routerMap = RpcauthModel::checkRouter($stdClass);
                $data = call_user_func_array($callBack, $params);
                if ($cached >= 0) {
                    $this->_setCacheData($cacheKey, json_encode($data), $cached);
                }
                $result->setResultData($data);
            }
        } catch (\Exception $e) {
            $result->setResultCode($e->getCode());
            $result->setResultMsg($e->getMessage());
            $errorData = array(
                'errorcode' => $e->getCode(),
                'errormsg' => $e->getMessage(),
                'traces' => $e->getTraceAsString(),
            );
            $routerMapTmp = $routerMap;
            $routerMapTmp['url'] = strtolower(\Yaf\Dispatcher::getInstance()->getRequest()->getRequestUri());
            $this->_errorCallback($errorData, $logParams, $routerMapTmp);
            unset($routerMapTmp);
        }
        $result->setResultTime(round(microtime(true) - $startTime, 5));
        Registry::set('rpcParams', $logParams);
        return json_encode($result->getResult());
    }
}