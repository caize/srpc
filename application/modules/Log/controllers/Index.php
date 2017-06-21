<?php
/**
 * Created by l.gang06@yahoo.com
 * User: l.gang06@yahoo.com
 * Date: 2017/5/31
 * Time: 15:40
 */
use Api\Controller\Base;
use Log\LogModel;

class IndexController extends Base
{
    public function serviceAction()
    {
        $startTime = date('Y-m-d', strtotime("-1 week")) . date(' H:i:s');
        $endTime = date('Y-m-d H:i:s');
        $this->getView()->assign('startTime', $startTime);
        $this->getView()->assign('endTime', $endTime);
        $this->display();
    }

    public function serviceloglistAction()
    {
        $type = $this->getRequest()->getParam('type');
        $this->getView()->assign('type', $type);
        $this->getView()->assign('startDate', $this->getRequest()->getParam('startDate'));
        $this->getView()->assign('endDate', $this->getRequest()->getParam('endDate'));
        $this->getView()->assign('ip', $this->getRequest()->getParam('ip'));
        $this->getView()->assign('url', $this->getRequest()->getParam('url'));
        $this->getView()->assign('appid', $this->getRequest()->getParam('appid'));
        $this->display();
    }

    public function applogAction()
    {
        $this->display();
    }

    public function querydetailAction()
    {
        $startDate = $this->getRequest()->getParam('startDate');
        $endDate = $this->getRequest()->getParam('endDate');
        $ip = $this->getRequest()->getParam('ip');
        $url = $this->getRequest()->getParam('url');
        $appid = $this->getRequest()->getParam('appid');
        $logModel = new LogModel();
        $type = $this->getRequest()->getParam('type');
        $logs = $logModel
            ->getServiceLogDetailByType($type, $startDate, $endDate, $ip, $url, $appid, $start = 0, $end = 5000);
        $this->_changeToJson();
        $this->_view->assign('errorcode', 0);
        $this->_view->assign('result', $logs);
        $this->display();
    }

    /**
     * 查看系统日志的详情信息接口
     */
    public function queryappdetailAction()
    {
        $startDate = $this->getRequest()->getParam('startDate');
        $endDate = $this->getRequest()->getParam('endDate');
        $logModel = new LogModel();
        $logs = $logModel->getAppLogDetailByType($startDate, $endDate, $start = 0, $end = 9000);
        $totalNumber = $logModel->getAppLogTotalNumberByType($startDate, $endDate);
        $this->_changeToJson();
        $this->_view->assign('total', $totalNumber);
        $this->_view->assign('errorcode', 0);
        $this->_view->assign('result', $logs);
        $this->display();
    }

    public function querylogapiAction()
    {
        $startDate = $this->getRequest()->getParam('startDate');
        $endDate = $this->getRequest()->getParam('endDate');
        $ip = $this->getRequest()->getParam('ip');
        $url = $this->getRequest()->getParam('url');
        $appid = $this->getRequest()->getParam('appid');
        $logModel = new LogModel();
        $res = $logModel->query($startDate, $endDate, $ip, $url, $appid);
        $this->_changeToJson();
        $this->_view->errorcode = 0;
        $this->_view->errormsg = '查询成功';
        $this->_view->result = $res;
        $this->display();
    }

    public function servicelogdetailAction()
    {
        $logId = $this->getRequest()->getParam('logId');
        $logModel = new LogModel();
        $logInfo = $logModel->getLogById($logId);
        if ($logInfo['access'] == 0) {
            if ($logInfo['msg'] != 'null') {
                $logInfo['msg'] = addslashes(base64_decode($logInfo['msg']));
            }
            if ($logInfo['traces'] != 'null') {
                $logInfo['traces'] = addslashes(base64_decode($logInfo['traces']));
            }

            $this->getView()->assign('show', 1);
        } else {
            $this->getView()->assign('show', 0);
        }
        if ($logInfo['params'] != 'null') {
            $params = json_decode(base64_decode($logInfo['params']), true);
            if (gettype($params) == 'array') {
                $logInfo['params'] = base64_decode($logInfo['params']);
                $this->getView()->assign('showjson', 1);
            } else {
                $logInfo['params'] = urldecode(base64_decode($logInfo['params']));
                $this->getView()->assign('showjson', 0);
            }
        }
        $logInfo['msg'] = htmlentities($logInfo['msg']);
        $this->getView()->assign('log', $logInfo);
        $this->display();
    }

    /**
     *  获取系统错误日志接口
     */
    public function querysyslogapiAction()
    {
        $startDate = $this->getRequest()->getParam('startDate');
        $endDate = $this->getRequest()->getParam('endDate');
        $logModel = new LogModel();
        $res = $logModel->queryApplog($startDate, $endDate);
        $this->_changeToJson();
        $this->_view->errorcode = 0;
        $this->_view->errormsg = '查询成功';
        $this->_view->result = $res;
        $this->display();
    }

}