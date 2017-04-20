<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/28
 * Time: 10:48
 * desc: rpc 继承控制器基础类
 */
namespace Api\Controller;
use Admin\LoginModel;
use \Yaf\Controller_Abstract;
use Yaf\Registry;

class Base extends Controller_Abstract
{
    protected $_runStartTime = null;
    /**
     * @var \Swoole\Http\Response
     */
    protected $_swooleResponse = null;
    public $baseUrl = null;
    public function init()
    {
        //swoole 下必须加上
        $this->_view->clear();
        $this->_runStartTime = microtime(true);
        //swoole获取host HTTP_ORIGIN
        $this->baseUrl = \Yaf\Registry::get('projectBaseUrl');

        //增加简单的登录验证
        $loginModel = new LoginModel();
        if (!$loginModel->check()) {
            $this->redirect('/admin/login/login/');
            return;
        }
    }
    protected function _changeToJson()
    {
        $this->_view = new \Api\View\Json();
        return $this;
    }

    public function display($tpl = null, array $parameters = NULL)
    {
        $this->_view->times = round(microtime(true) - $this->_runStartTime, 5);
        $this->_view->baseUrl = $this->baseUrl . '/' . strtolower($this->_request->getModuleName());
        if ($tpl === null) {
            $tpl = strtolower($this->_request->getActionName());
        }
        parent::display($tpl, $parameters);
    }

    public function redirect($url, $mode = 302)
    {
        /**
         * var ;
         */
        $url = strtolower($url);
        if (!Registry::has('swooleResponse')) {
            parent::redirect($url);
        } else {
            $response = Registry::get('swooleResponse');
            $response->status($mode);
            $response->header('location', $url);
        }
    }
}