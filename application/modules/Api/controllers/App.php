<?php
/**
 * Created by l.gang06@yahoo.com
 * User: l.gang06@yahoo.com
 * Date: 2017/6/16
 * Time: 11:11
 */
use Api\Controller\Base;
use Log\AppModel;

class AppController extends Base
{
    public function indexAction()
    {
        $this->display();
    }

    public function queryappapiAction()
    {
        $appid = $this->getRequest()->getParam('appid');
        $appModel = new AppModel();
        $app = $appModel->getAppInfoByid($appid);
        if (empty($app)) {
            $res['errorcode'] = -1;
            $res['errormsg'] = '信息不存在';
        } else {
            $res['errorcode'] = 0;
            $res['errormsg'] = '查询成功';
            $res['result'] = $app;
        }
        echo json_encode($res);
    }
}