<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/5
 * Time: 16:21
 */
namespace Externalapi;
use \Api\Iface\Rpcmodel as IrpcModel;
use Yaf\Request\Http;
use Yaf\Registry;
use Api\Globals\Defined;
use \Api\Client\Http as ApiHttp;
use Rpc\RpcauthModel;
use \Common\Result\HttpModel as ResultHttpModel;
class RpchttpModel implements IrpcModel
{
    protected $_params = array();
    protected $_returnType = 'json';
    protected $_authToken = null;
    public function __set($name, $value)
    {
        $v = '_' . $name;
        $this->$v = $value;
    }
    public function __setParams($params)
    {
        if (!is_array($params)) {
            return false;
        }
        $this->_params = $params;
        return $this;
    }

    protected function authThirdType()
    {
        return Defined::OTHER_AUTH_DEFAULT;
    }
    /**
     * @param array $params
     *     对应http请求的参数
     *     有两种方式进行交互
     *      1. 直接绑定在请求url中如 http://api.10jqka.com.cn/api/test/test?test=1&auth-token=****,
     *          这种方式 $params可以不穿
     *      2. 调用方法时传入，如 http://api.10jqka.com.cn/api/test/test?auth-token=****,
     *          指定call方法时传入 array('test' => 1);
     *      注:如果同时传入http参数和call中的params参数，相同的key以params参数为准
     * * @desc
     *  注：认证token，在url中增加auth-token参数，此只针对需要认证的服务有效
     * @return json|xml
     */
    public function send($params = array())
    {
        if (!empty($params) && is_array($params)) {
            foreach ($params as $key => $val) {
                $this->_params[$key] = $val;
            }
        }
        try {
            $routerMap = RpcauthModel::getRouterMap($this->_authToken);
            $forwordRouterMap = '';
            if (isset($this->_params['@reqtype'])) {
                $forwordRouterMap = $this->_params['@reqtype'];
                unset($this->_params['@reqtype']);
            }
            switch ($this->authThirdType()) {
                case Defined::OTHER_AUTH_DEFAULT:
                    break;
                case Defined::OTHER_AUTH_IWENCAI:
                    if (!isset($routerMap['appid'])) {
                        throw new \Exception(
                            Defined::MSG_API_AUTH_FAILED_THIRD_NOT_FOUND, Defined::CODE_API_AUTH_FAILED_THIRD_NOT_FOUND
                        );
                    }
                    $headerInfo = RpcauthModel::getThirdAuthInfo(
                        $routerMap['appid'], $this->authThirdType(), $routerMap['url']
                    );
                    $forwordRouterMap = ltrim($forwordRouterMap, '/');
                    if (!empty($forwordRouterMap)) {
                        $routerMap['url'] .= '/' . $forwordRouterMap;
                    }
                    if ($headerInfo) {
                        $routerMap = array_merge($routerMap, $headerInfo);
                    }
                    break;
            }
            $data = $this->_sendApiHttpObj($routerMap);
            $return =  $data->getResult();
            $return['result'] = mb_convert_encoding($return['result'], "UTF-8", "gbk,gb2312");
            return json_encode($return);
        } catch (\Exception $e) {
            return json_encode(
                array(
                    'errorcode' => $e->getCode(),
                    'errormsg' => $e->getMessage()
                )
            );
        }
    }

    protected function _sendApiHttpObj($routerMap)
    {
        $result = new ResultHttpModel();
        $http = new ApiHttp();
        if (empty($routerMap['url'])) {
            $result->setResultMsg(Defined::MSG_API_AUTH_FAILED_NOT_FOUND_MAP_URL);
            $result->setResultCode(Defined::CODE_API_AUTH_FAILED_NOT_FOUND_MAP_UR);
            return $result;
        }
        $http->setUrl($routerMap['url']);
        if (isset($this->_params['auth-token'])) {
            unset($this->_params['auth-token']);
        }
        $http->setParamsPost($this->_params)->setMethod($http::HTTP_POST);
        if ($routerMap['host']) {
            $parseInfo = parse_url($routerMap['url']);
            $url = $parseInfo['scheme'] . '://' . $routerMap['host'];
            $host = $parseInfo['host'];
            if (isset($parseInfo['path'])) {
                $url .= $parseInfo['path'];
            }
            if (isset($parseInfo['query'])) {
                $url .= '?' . $parseInfo['query'];
            }
            $http->setUrl($url);
            $http->setHeader('Host', $host);
        }
        if (isset($routerMap['header'])) {
            foreach ($routerMap['header'] as $h => $v) {
                $http->setHeader($h, $v);
            }
        }

        if (isset($routerMap['cookie'])) {
            $http->setCookie($routerMap['cookie']);
        }
        $data = $http->request();
        $result->setResultTime($http->runtime);
        if (false === $data) {
            $result->setResultMsg($http->errMsg);
            $result->setResultCode(Defined::CODE_API_HTTP_FAILED_CURL);
            return $result;
        }
        return $result->setResultData($data);
    }
}