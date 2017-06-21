<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/5/27
 * Time: 10:39
 */

namespace Localapi\Rpcapi;
use Api\Globals\Defined;
use Rpc\RpclocalModel;


class AppCacheManagerModel extends RpclocalModel
{
    /**
     * 获取问财token
     * @param $params array
     *      appid =>
     *      secret =>
     *      reload => 1 重新生成
     */
    public function iwencaiOpenapiToken($params)
    {
        return $this->_send(
            function ($params) {
                if (!isset($params['appid']) || !isset($params['secret'])) {
                    throw new \Exception(
                        Defined::MSG_API_PARAM_CHECK_FAILED_IFINDSERVICEAUTH, Defined::CODE_API_PARAM_CHECK_FAILED
                    );
                }
                $params['reload'] = isset($params['reload']) && $params['reload'] == 1 ? true : false;
                $routerModel = new \Datawarehouse\Serviceapi\RouterModel();
                $data = $routerModel->getRouterMap('api/iwencai/openapi');
                $tokenObj = new \Auth\TokenIwencaiModel();
                $result = $tokenObj->getToken($params['appid'], $params['secret'], $data->url, $params['reload']);
                if ($result->getResultCode() != 0) {
                    throw new \Exception($result->getResultMsg(), $result->getResultCode());
                }
                return $result->getResultData();
            }, array($params)
        );
    }

    /**
     * 获取rpc token
     * @param $params array
     *      appid =>
     *      secret =>
     *      reload => 1 重新生成
     */
    public function rpcToken($params)
    {
        return $this->_send(
            function ($params) {
                if (!isset($params['appid']) || !isset($params['secret'])) {
                    throw new \Exception(
                        Defined::MSG_API_PARAM_CHECK_FAILED_IFINDSERVICEAUTH, Defined::CODE_API_PARAM_CHECK_FAILED
                    );
                }
                $params['reload'] = isset($params['reload']) && $params['reload'] == 1 ? true : false;
                $redisConfig = \Yaf\Registry::get('redisConfig');
                $cacheManager = \Yaf\Registry::get('cacheManager');

                if (!$params['reload']) {
                    return $cacheManager->get($redisConfig['api']['tokenappid'] . $params['appid']);
                }
                $tokenModel = new \Auth\TokenModel();
                $result = $tokenModel->getToken($params['appid'], $params['secret']);
                $data = $result->getResultData();
                return $data['token'];
            }, array($params)
        );
    }

    /**
     * 删除key
     * @key $key string
     */
    public function delCache($key)
    {
        return $this->_send(
            function ($key) {
                if (empty($key)) {
                    throw new \Exception(
                        Defined::MSG_API_PARAM_CHECK_FAILED_IFINDSERVICEAUTH, Defined::CODE_API_PARAM_CHECK_FAILED
                    );
                }
                \Yaf\Registry::get('cacheManager')->del($key);
                return '';
            }, array($key)
        );
    }

    /**
     * 删除key
     * @key 获取key
     */
    public function getCache($key)
    {
        return $this->_send(
            function ($key) {
                if (empty($key)) {
                    throw new \Exception(
                        Defined::MSG_API_PARAM_CHECK_FAILED_IFINDSERVICEAUTH, Defined::CODE_API_PARAM_CHECK_FAILED
                    );
                }
                return \Yaf\Registry::get('cacheManager')->get($key);
            }, array($key)
        );
    }
}