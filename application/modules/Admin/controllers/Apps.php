<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 15:26
 */
use Api\Controller\Base;
use Admin\AppsModel;
use Common\SendmailModel;

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
        $this->display();
    }

    public function applyaddapiAction()
    {
        $model = new AppsModel();
        $result = $model->appApply($this->_request->getParams());
        if (!$result->getResultCode()) {
            $appName = $this->getRequest()->getParam('appName');
            $email = $this->getRequest()->getParam('email');
            $serveralert = $this->getRequest()->getParam('serveralert');
            $sendMailModel = new SendmailModel();
            $people = array('l.gang06@yahoo.com', 'l.gang06@yahoo.com');
            $title = 'Webrpc新应用申请';
            $content = '<br/>申请信息<br/>'
                . '应用名称:' . $appName . '<br/>' . '申请者邮箱:' . $email . '<br/>'
                . 'serveralert:' . $serveralert . '<br/>'
                . '--来自--' . APPLICATION_ENV;
            $sendMailModel->sendHtmlMailToPeople($people, $title, $content);
        }
        $res['errormsg'] = $result->getResultMsg();
        $res['errorcode'] = $result->getResultCode();
        echo json_encode($res);
    }

    /**
     * 应用申请通过接口
     */
    public function reviewAction()
    {
        $model = new AppsModel();
        $result = $model->review($this->_request->getParam('id', 0));
        if (!$result->getResultCode()) {
            $applyMsg = $model->getApplyMessageByid($this->_request->getParam('id', 0));
            $people = array($applyMsg['applyemail']);
            $subject = $applyMsg['appname'] . '申请反馈';
            $body = "<br/>您好：<br/>管理员通过了您的\"" . $applyMsg['appname'] . "\"应用申请。";
            $sendMailModel = new SendmailModel();
            $sendMailModel->sendHtmlMailToPeople($people, $subject, $body);
        }
        $res['errorcode'] = $result->getResultCode();
        $res['errormsg'] = $result->getResultMsg();
        echo json_encode($res);
    }

    /**
     * 应用申请驳回接口
     */
    public function unreviewAction()
    {
        $model = new AppsModel();
        $result = $model->unReview($this->_request->getParam('id', 0));
        if (!$result->getResultCode()) {
            $applyMsg = $model->getApplyMessageByid($this->_request->getParam('id', 0));
            $people = array($applyMsg['applyemail']);
            $subject = $applyMsg['appname'] . '申请反馈';
            $body = "<br/>您好：<br/>管理员驳回了您的" . $applyMsg['appname']
                . '应用申请。<br/>原因：' . $this->_request->getParam('reason', '未填写');
            $sendMailModel = new SendmailModel();
            $sendMailModel->sendHtmlMailToPeople($people, $subject, $body);
        }
        $res['errorcode'] = $result->getResultCode();
        $res['errormsg'] = $result->getResultMsg();
        echo json_encode($res);
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

    public function updateinfoAction()
    {
        $model = new AppsModel();
        if ($this->_request->isPost()) {
            $result = $model->updateInfo($this->_request->getParams());
            $view = $this->getView();
            $view->errorMsg = $result->getResultMsg();
        }
        $row = $model->getInfo($this->_request->getParam('appid', ''));
        $view = $this->getView();
        $view->info = $row;
        $this->display();
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

    public function getbindinfoAction()
    {
        $appid = $this->_request->getParam('appid', 0);
        $type = $this->_request->getParam('type', '');
        $appModel = new \Admin\AppsModel();
        $bindList = $appModel->bindThirdList($appid);
        $this->_changeToJson();
        $view = $this->getView();
        if (isset($bindList[$type])) {
            $view->result = json_decode($bindList[$type]['content'], true);
        }
        $this->display();
    }
}
