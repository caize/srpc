<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 15:26
 */
use Api\Controller\Base;
use Admin\RouterModel;
class RouterController extends Base
{
    public function listAction()
    {
        $model = new RouterModel();
        $lists = $model->getList();
        $view = $this->getView();
        $view->list = $lists;
        $this->display();
    }

    public function addAction()
    {
        if ($this->_request->isPost()) {
            $model = new RouterModel();
            $result = $model->updateService($this->_request->getParams());
            $view = $this->getView();
            $view->errorMsg = $result->getResultMsg();
        }
        $serviceModel = new Admin\ServiceModel();
        $list = $serviceModel->getList();
        $view = $this->getView();
        $view->serviceList = $list;
        $this->display();
    }

    public function delAction()
    {
        $model = new RouterModel();
        $result = $model->del($this->_request->getParam('id', 0));
        $this->redirect('/admin/router/list');
    }
}

