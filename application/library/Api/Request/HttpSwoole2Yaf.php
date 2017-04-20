<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/29
 * Time: 21:29
 */
namespace Api\Request;
use \Yaf\Request\Http;
use \Yaf\Registry;
use \Swoole\Http\Request;
class HttpSwoole2Yaf
{
    private $_swooleReq ;
    private $_yafReq;
    public function __construct(Request $request)
    {
        $this->_swooleReq = $request;
        $this->_init();
    }

    public function __destruct()
    {
        //初始化
        foreach ($_SERVER as $key => $val) {
            if (strpos($key, 'HTTP_') === 0) {
                unset($_SERVER[$key]);
            }
        }
        $_COOKIE = array();
    }
    private function _init()
    {
        $params = array();
        if (isset($this->_swooleReq->get)) {
            $params = $this->_swooleReq->get;
        }
        if (isset($this->_swooleReq->post)) {
            $params = array_merge($params, $this->_swooleReq->post);
        }
        $this->_setBaseRouter($this->_swooleReq->server['request_uri'])->_setUriParams($params);
        $this->getYafHttp()->method = $this->_swooleReq->server['request_method'];
        $this->_initHeader()->_initCookie();
        $this->getYafHttp()->setDispatched(true);
        $this->getYafHttp()->setRouted(true);
    }

    protected function _initHeader()
    {

        if (isset($this->_swooleReq->header)) {
            foreach ($this->_swooleReq->header as $key => $val) {
                $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
                if (!isset($_SERVER[$headerKey])) {
                    $_SERVER[$headerKey] = $val;
                }
            }
        }
        return $this;
    }

    protected function _initCookie()
    {
        if (isset($this->_swooleReq->cookie)) {
            foreach ($this->_swooleReq->cookie as $key => $val) {
                if (!isset($_COOKIE[$key])) {
                    $_COOKIE[$key] = $val;
                }
            }
        }
        return $this;
    }

    /**
     * @var \Yaf\Request\Http
     */
    public function getYafHttp()
    {
        if (null === $this->_yafReq) {
            $this->_yafReq = new Http();
        }
        return $this->_yafReq;
    }

    /**
     * @param $baseUriArr
     * @desc 设置module controller action
     */
    protected function _setBaseRouter($baseUri)
    {
        $baseUriArr = explode('/', trim(strtolower($baseUri), '/'));
        if (empty(array_filter($baseUriArr))) {
            return true;
        }
        $this->getYafHttp()->setModuleName($baseUriArr[0]);
        if (isset($baseUriArr[1])) {
            $this->getYafHttp()->setControllerName($baseUriArr[1]);
        } else {
            $this->getYafHttp()->setControllerName('index');
        }
        if (isset($baseUriArr[2])) {
            $this->getYafHttp()->setActionName($baseUriArr[2]);
        } else {
            $this->getYafHttp()->setActionName('index');
        }
        $this->getYafHttp()->setRequestUri(
            strtolower(
                $this->getYafHttp()->getModuleName() . '/' . $this->getYafHttp()->getControllerName()
                . '/' . $this->getYafHttp()->getActionName() . '/'
            )
        );
        return $this;
    }

    protected function _setUriParams($params)
    {
        if (empty($params['_lang'])) {
            Registry::set('_lang', 'yar');
        } else {
            Registry::set('_lang', strtolower($params['_lang']));
        }
        foreach ($params as $key => $val) {
            $this->getYafHttp()->setParam($key, $val);
        }
    }
}