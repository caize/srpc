<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/19
 * Time: 17:39
 */
use Api\Controller\Base;
class LoginController extends Base
{
    public function loginAction()
    {
        if ($this->_request->isPost()) {
            $model = new \Admin\LoginModel();
            if ($model->doLogin($this->_request->getParam('username'), $this->_request->getParam('userpwd'))) {
                $this->redirect('/admin/service/index');
                return ;
            }
        }
        $this->display();
    }
}