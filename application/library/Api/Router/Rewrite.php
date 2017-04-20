<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/23
 * Time: 21:29
 */
namespace Api\Router;
use \Yaf\Route_Interface;
use \Yaf\Request_Abstract;
use \Yaf\Registry;
class Rewrite implements \Yaf\Route_Interface
{
    /**
     * @var \Yaf\Request\Http;
     */
    protected $_req ;
    public function route($req)
    {
        $this->_req = $req;
        $uri = $this->_req->getRequestUri();
        $this->_setBaseRouter($uri);
        $this->_setUriParams($_REQUEST);
    }

    public function assemble(array $info, array $query = array())
    {
        $uri = '';
        foreach ($info as $val) {
            $uri .= $val . '/';
        }
        return $uri . '?' . http_build_query($query);
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
        $this->_req->setModuleName($baseUriArr[0]);


        if (isset($baseUriArr[1])) {
            $this->_req->setControllerName($baseUriArr[1]);
        } else {
            $this->_req->setControllerName('index');
        }
        if (isset($baseUriArr[2])) {
            $this->_req->setActionName($baseUriArr[2]);
        } else {
            $this->_req->setActionName('index');
        }
        $this->_req->setRequestUri(
            strtolower(
                $this->_req->getModuleName() . '/' . $this->_req->getControllerName()
                . '/' . $this->_req->getActionName() . '/'
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
            $this->_req->setParam($key, $val);
        }
    }
}