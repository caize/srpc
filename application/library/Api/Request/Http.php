<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/5/2
 * Time: 14:23
 */
namespace Api\Request;
class Http
{
    protected $_requestUri;
    protected $_method;
    protected $_params;

    public function getRequestUri()
    {
        return $this->_requestUri;
    }

    public function setRequestUri($uri)
    {
        $this->_requestUri = $uri;
        return $this;
    }

    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }
    public function getMethod()
    {
        return $this->_method;
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function setParams($params)
    {
        $this->_params = $params;
        return $this;
    }

    public function setParam($key, $val)
    {
        $this->_params[$key] = $val;
        return $this;
    }

    public function getParam($key, $default = '')
    {
        if (isset($this->_params[$key]))
            return $this->_params[$key];
        return $default;
    }
}