<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 13:19
 */
namespace Admin;

use \Illuminate\Database\Capsule\Manager as DB;
use Common\ResultModel;
use \Api\Cookie;
use \Yaf\Session;
class LoginModel
{
    private $_user = 'admin';
    private $_pwd = 'admin123';
    public function __construct()
    {
        $this->_pwd = APPLICATION_ENV == 'production' ? sha1('admin_' . date('Ymd')) : $this->_pwd;
    }

    public function check()
    {
        $modulesName = strtolower(\Yaf\Dispatcher::getInstance()->getRequest()->module);
        $controllerName = strtolower(\Yaf\Dispatcher::getInstance()->getRequest()->controller);
        if ($modulesName == 'admin' && $controllerName == 'login') {
            return true;
        } elseif ($modulesName == 'admin') {
            if (
                isset($_COOKIE['userSwoole'])
                && $_COOKIE['userSwoole'] == $this->getEncryPwd($this->_user, $this->_pwd)
            ) {
                return true;
            }
            return false;
        }
        return true;
    }

    protected function getEncryPwd($user, $pwd)
    {
        return md5($user . '_' . $pwd);
    }

    public function doLogin($userName, $userPwd)
    {
        if ($this->getEncryPwd($userName, $userPwd) == $this->getEncryPwd($this->_user, $this->_pwd)) {
            Cookie::set('userSwoole', $this->getEncryPwd($userName, $userPwd));
            return true;
        }
        return false;
    }
}