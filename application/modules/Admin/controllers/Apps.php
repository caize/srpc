<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 15:26
 */
use Api\Controller\Base;
use Admin\AppsModel;
class AppsController extends Base
{
    public function applylistAction()
    {
        $model = new AppsModel();
        $lists = $model->applyList();
        $view = $this->getView();
        $view->list = $lists;
        $this->display();
    }

    public function applyaddAction()
    {
        if ($this->_request->isPost()) {
            $model = new AppsModel();
            $result = $model->appApply($this->_request->getParams());
            $view = $this->getView();
            $view->errorMsg = $result->getResultMsg();
        }
        $this->display();
    }

    public function reviewAction()
    {
        $model = new AppsModel();
        $result = $model->review($this->_request->getParam('id', 0));
        if (!$result->isValid()) {
            echo "<script>alert('{$result->getResultMsg()}')</script>";
        }
        $flag = $this->redirect(
            '/' . $this->_request->getModuleName() . '/' . $this->_request->getControllerName() . '/applylist'
        );
        echo 'ok';
        return false;
    }

    public function applyeditAction()
    {

    }

    public function indexAction()
    {
        $model = new AppsModel();
        $lists = $model->appList();
        $view = $this->getView();
        $view->list = $lists;
        $this->display();
    }

    public function addAction()
    {

    }

    public function addserviceAction()
    {
        $model = new AppsModel();
        if ($this->_request->isPost()) {
            $result = $model->addService($this->_request->getParams());
            $view = $this->getView();
            $view->errorMsg = $result->getResultMsg();
        }
        $appid = $this->_request->getParam('appid', null);
        $row = $model->getInfo($appid);
        if (empty($row)) {
            echo '操作失败';
            return;
        }
        $serviceModel = new Admin\ServiceModel();
        $list = $serviceModel->getAuthList();
        $view = $this->getView();
        $hasService = $model->getService($appid);
        $view->serviceList = $list;
        $view->appname = $row['appname'];
        $view->hasService = array_flip($hasService);
        $view->appid = $appid;
        $this->display();
    }

    public function bindthirdauthAction()
    {
        $appid = $this->_request->getParam('appid', '');
        if ($this->_request->isPost()) {
            $this->_changeToJson();
            $appModel = new \Admin\AppsModel();
            $result = $appModel->addBindThird($this->_request->getParams());
            $view = $this->getView();
            $view->errorcode = $result->getResultCode();
            $view->errormsg = $result->getResultMsg();
            $this->display();
            return false;
        }
        if (empty($appid)) {
            echo '操作失败';
            return false;
        }
        $appModel = new \Admin\AppsModel();
        $rows = $appModel->bindThirdList($appid);
        $authList = \Api\Globals\Defined::getOtherAuthArray();
        $view = $this->getView();
        $view->authList = $authList;
        $view->hasBindThirdList = $rows;
        $view->appid = $appid;
        $this->display();
    }

    public function delbindthirdAction()
    {
        $appModel = new \Admin\AppsModel();
        $rows = $appModel->delBindThird(
            $this->_request->getParam('appid', ''),
            $this->_request->getParam('type', '')
        );
        $this->redirect('/admin/apps/bindthirdauth/?appid=' . $this->_request->getParam('appid', ''));
    }
}
