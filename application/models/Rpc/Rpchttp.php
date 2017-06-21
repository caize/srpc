<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/5
 * Time: 16:21
 */
namespace Rpc;

use Api\Globals\Functions;
use \Api\Iface\Rpcmodel as IrpcModel;
use \Api\Globals\Defined;
use \Common\Result\HttpModel as ResultHttpModel;
use Common\ResultModel;
use Yaf\Registry;
abstract class RpchttpModel extends RpcbaseModel implements IrpcModel
{
    const DATATYPE_JSON = 'json';
    const DATATYPE_PHP = 'php';
    const DATATYPE_STRING = 'string';
    const DATATYPE_XML = 'xml';
    protected $_returnType = self::DATATYPE_JSON;
    protected $_methodType = 'POST';
    protected $_timeout = null;
    public function __setParams($params)
    {
        if (!is_array($params)) {
            return false;
        }
        $this->_params = $params;
        return $this;
    }

    protected function _setTimeout($timeout)
    {
        $this->_timeout = $timeout;
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
     *      3. 指定协议
     *          @appid 根据接口认证方式可以走ip白名单认证
     *          @cookie 第三方需要的用户登录cookie
     *          @cached 开启缓存 值为缓存时间，设置为0，缓存时间为半小时
     *      注:如果同时传入http参数和call中的params参数，相同的key以params参数为准
     * * @desc
     *  注：认证token，在url中增加auth-token参数，此只针对需要认证的服务有效
     * @return json
     */
    public function send($params = array())
    {
        $params = Functions::apiParamsCheck($params);
        if (!empty($params) && is_array($params)) {
            foreach ($params as $key => $val) {
                $this->_params[$key] = $val;
            }
        }
        $this->_params = $this->_foundAppidFromParams($this->_params);
        /**
         * 修改params用于swoole 记录日志
         */
        $rpcParams = $this->_params;
        $rpcParams['@appid'] = $this->_appid;
        Registry::set('rpcParams', $rpcParams);
        if (isset($params['@timeout'])) {
            $this->_setTimeout($params['@timeout'] < 300 ? $params['@timeout'] : 300);
        }
        //var_dump(\Yaf\Dispatcher::getInstance()->getRequest()->getParams());
        //var_dump(\Yaf\Dispatcher::getInstance()->getRequest()->parmas);
        /*
         * end
         */
        try {
            $cached = -1;
            if (isset($this->_params['@cached'])) {
                $cached = intval($this->_params['@cached']);
                unset($this->_params['@cached']);
                $cacheKey = $this->_encryptCacheKey($this->_params);
                $cacheData = $this->_getCacheData($cacheKey);
                if ($cacheData) {
                    $result = new ResultHttpModel();
                    $result->setResultData(json_decode($cacheData, true));
                    $result->setResultTime(round(microtime(true) - $this->_startTime, 5));
                    return json_encode($result->getResult());
                }
            }
            $routerMap = RpcauthModel::getRouterMapInfo();
            $stdClass = new \stdClass();
            $stdClass->token = $this->_authToken;
            $stdClass->appid = $this->_appid;
            $stdClass->routerMap = $routerMap;
            switch ($this->authThirdType()) {
                case Defined::OTHER_AUTH_DEFAULT:
                case Defined::OTHER_AUTH_LOCAL:
                    $routerMap = RpcauthModel::checkRouter($stdClass);
                    break;
                case Defined::OTHER_AUTH_IWENCAI:
                    /**
                     * 20170511 增加参数传递认证
                     */
                    if (isset($this->_params['appid'])) {
                        RpcauthModel::checkAppid($this->_appid);
                        $headerInfo['header']['Access-Token'] = RpcauthModel::iwencaiApiToken(
                            $this->_params['appid'], $this->_params['secret'], $routerMap['url']
                        );
                        //删除appid和secret请求信息
                        unset($this->_params['appid']);
                        unset($this->_params['secret']);
                    } else {
                        $routerMap = RpcauthModel::checkRouter($stdClass);
                        if (isset($routerMap['appid'])) {
                            $headerInfo = RpcauthModel::getThirdAuthInfo(
                                $routerMap['appid'], $this->authThirdType(), $routerMap['url']
                            );
                        } else {
                            throw new \Exception(
                                Defined::MSG_API_AUTH_FAILED_THIRD_NOT_FOUND,
                                Defined::CODE_API_AUTH_FAILED_THIRD_NOT_FOUND
                            );
                        }
                    }
                    if ($headerInfo) {
                        $routerMap = array_merge($routerMap, $headerInfo);
                    }
                    break;
            }

            $forwordRouterMap = '';
            if (isset($this->_params['@reqtype'])) {
                $forwordRouterMap = $this->_params['@reqtype'];
                unset($this->_params['@reqtype']);
            }
            if (!empty($forwordRouterMap)) {
                $routerMap['url'] .= '/' . ltrim($forwordRouterMap, '/');
            }
            $data = $this->_sendApiHttpObj($routerMap);
            $parseResult = $this->_parseResult($data);
            $return =  $parseResult->getResult();


//            if (self::DATATYPE_JSON != $this->_returnType) {
//                $encoding = mb_detect_encoding($return['result'], array('UTF-8', 'GBK', 'GB2312'));
//                if ($encoding !== 'UTF-8') {
//                    if (self::DATATYPE_PHP == $this->_returnType) {
//                        $tmp = unserialize($return['result']);
//                        $return['result'] = serialize(Functions::iconvArr($encoding, 'UTF-8', $tmp));
//                    } else {
//                        $return['result'] = iconv($encoding, 'UTF-8', $return['result']);
//                    }
//                }
//            }
            $returnData = json_encode($return);
            if ($return['errorcode'] != 0) {
                $this->_errorCallback($return, $params, $routerMap);
            } elseif ($cached >= 0 && !empty($return)) {
                $this->_setCacheData($cacheKey, json_encode($return['result']), $cached);
            }
            return $returnData;
        } catch (\Exception $e) {
            if ($e->getCode() != Defined::CODE_API_AUTH_FAILED_NOT_FOUND_TOKEN) {
                $errorData = array(
                    'errorcode' => $e->getCode(),
                    'errormsg' => $e->getMessage(),
                    'traces' => $e->getTraceAsString(),
                );
                $routerMapTmp = $routerMap;
                $routerMapTmp['url'] = strtolower(\Yaf\Dispatcher::getInstance()->getRequest()->getRequestUri());
                $this->_errorCallback($errorData, $params, $routerMapTmp);
                unset($routerMapTmp);
            }
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
        /**
         * swoole协程与yaf和yar都有兼容性问题，暂时继续使用同步模式
         */
        if (false && defined('SWOOLE_SERVER') && SWOOLE_SERVER == 'HTTP') {
            $http = new \Api\Client\Coroutine\Http();
            /**
             * 使用协程后无法执行dispacher后的代码，所以这里设置协程标记，
             * 程序中判断标识使用 swoole的respnse输出结果
             */
            \Yaf\Registry::set('coroutineHttp', 1);
        } else {
            $http = new \Api\Client\Http();
        }
        if (empty($routerMap['url'])) {
            $result->setResultMsg(Defined::MSG_API_AUTH_FAILED_NOT_FOUND_MAP_URL);
            $result->setResultCode(Defined::CODE_API_AUTH_FAILED_NOT_FOUND_MAP_UR);
            return $result;
        }
        $http->setUrl($routerMap['url']);
        if (isset($this->_params['auth-token'])) {
            unset($this->_params['auth-token']);
        }
        if ($this->_methodType == $http::HTTP_POST) {
            $http->setParamsPost($this->_params)->setMethod($http::HTTP_POST);
        } else {
            $http->setParamsGet($this->_params)->setMethod($http::HTTP_GET);
        }
        if ($routerMap['host']) {
            $parseInfo = parse_url($routerMap['url']);
            if (!isset($parseInfo['scheme'])) {
                $url = 'http://' . $routerMap['host'];
            } else {
                $url = $parseInfo['scheme'] . '://' . $routerMap['host'];
            }
            if (isset($parseInfo['port'])) {
                $url .= ':' . $parseInfo['port'];
            }
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
        } elseif (isset($this->_params['@cookie'])) {
            $http->setCookie($this->_params['@cookie']);
            unset($this->_params['@cookie']);
        }
        if (isset($this->_params['@userAgent'])) {
            $http->setUserAgent($this->_params['@userAgent']);
        }

        if ($this->_timeout > 0) {
            $http->setTimeout($this->_timeout);
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

    protected function _parseResult($resultModel)
    {
        if (!$resultModel->isValid()) {
            return $resultModel;
        }
        $data = $resultModel->getResultData();
//        $encoding = mb_detect_encoding($data, array('UTF-8', 'GBK', 'GB2312'));
//        if ($encoding !== 'UTF-8') {
//            if (self::DATATYPE_PHP == $this->_returnType) {
//                $tmp = unserialize($return['result']);
//                $return['result'] = serialize(Functions::iconvArr($encoding, 'UTF-8', $tmp));
//            } else {
//                $return['result'] = iconv($encoding, 'UTF-8', $return['result']);
//            }
//        }
        switch ($this->_returnType) {
            case self::DATATYPE_JSON;
                $desc = json_decode($data, true);
                //数据解析失败抛出异常预警
                if ($desc === null) {
                    throw new \Exception(
                        Defined::MSG_API_PARSE_FAILED_DATA . ';json:' . $data,
                        Defined::CODE_API_PARSE_FAILED_DATA
                    );
                }
                break;
            case self::DATATYPE_PHP:
                $desc = unserialize($data);
                if ($desc === false && $data != 'b:0;') {
                    throw new \Exception(
                        Defined::MSG_API_PARSE_FAILED_DATA . ';serialize:' . $data,
                        Defined::CODE_API_PARSE_FAILED_DATA
                    );
                }
                $encoding = mb_detect_encoding($data, array('UTF-8', 'GBK', 'GB2312'));
                if ($encoding !== 'UTF-8') {
                    $desc = Functions::iconvArr($encoding, 'UTF-8', $desc);
                }
                break;
            default:
                $encoding = mb_detect_encoding($data, array('UTF-8', 'GBK', 'GB2312'));
                if ($encoding !== 'UTF-8') {
                    $desc = iconv($encoding, 'UTF-8', $data);
                    if ($this->_returnType == self::DATATYPE_XML) {
                        $desc = preg_replace('/encoding="(.*)"/', "encoding=\"UTF-8\"", $desc);
                    }
                } else {
                    $desc = $data;
                }
                break;
        }
        /**
         * xml 暂时原样返回
         */

        if ($this->_errorCodeKeyMap() === false || $this->_returnType == self::DATATYPE_XML) {
            $resultModel->setResultData($desc);
            return $resultModel;
        }
        if (
            isset($desc[$this->_errorCodeKeyMap()])
                && $desc[$this->_errorCodeKeyMap()] != $this->_errorCodeSuccessVal()
        ) {
            $resultModel->setResultMsg(
                isset($desc[$this->_errorMsgKeyMap()]) ? $desc[$this->_errorMsgKeyMap()] : ''
            );
            $resultModel->setResultCode($desc[$this->_errorCodeKeyMap()]);

            if (isset($desc[$this->_resultDataMap()])) {
                $resultModel->setResultData($desc[$this->_resultDataMap()]);
            } else {
                $resultModel->setResultData($desc);
            }
        } else {
            if (isset($desc[$this->_resultDataMap()])) {
                $resultModel->setResultData($desc[$this->_resultDataMap()]);
            } else {
                if (isset($desc[$this->_errorMsgKeyMap()])) {
                    unset($desc[$this->_errorMsgKeyMap()]);
                }
                if (isset($desc[$this->_errorCodeKeyMap()])) {
                    unset($desc[$this->_errorCodeKeyMap()]);
                }
                $resultModel->setResultData($desc);
            }
        }
        return $resultModel;
    }

    abstract protected function _errorCodeKeyMap();

    abstract protected function _errorCodeSuccessVal();

    abstract protected function _errorMsgKeyMap();

    abstract protected function _resultDataMap();
}
