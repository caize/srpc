<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 13:18
 */
use Api\Controller\Base;
use Admin\ServiceModel;
class ServiceController extends Base
{
    public function indexAction()
    {
        $model = new ServiceModel();
        $lists = $model->getList();
        $view = $this->getView();
        $view->list = $lists;
        $this->display();
    }

    public function addAction()
    {
        if ($this->_request->isPost()) {
            $model = new ServiceModel();
            $result = $model->updateService($this->_request->getParams());
            $view = $this->getView();
            $view->errorMsg = $result->getResultMsg();
        }
        $this->display();
    }

    public function editAction()
    {
        $model = new ServiceModel();
        if ($this->_request->isPost()) {
            $model = new ServiceModel();
            $result = $model->updateService($this->_request->getParams());
            $view = $this->getView();
            $view->errorMsg = $result->getResultMsg();
        }
        $row = $model->getInfo($this->_request->getParam('id', 0));
        $view = $this->getView();
        $view->row = $row;
        $this->display();
    }
}
