<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/6/14
 * Time: 10:01
 */

use Api\Controller\Base;
class ApiController extends Base
{
    /**
     * 服务注册
     */
    public function registerAction()
    {
        /**
         * 服务名称 servicename = 路由名称
         * 服务地址 serviceurl
         * host    host
         * wiki    wiki
         * router rpc路由
         *        api/固定
         *        controller + action
         *          model名称自动生成用action
         * returnType   json|php|string|xml
         * method   post | get
         * isauth false
         * ignore  忽略model错误
         * @desc
         *   示例
         *   http://lg.api.10jqka.com.cn/home/api/register?
         *   servicename=%E5%86%85%E7%BD%91%E6%B5%8B%E8%AF%95&serviceurl=http://hq.10jqka.com.cn/quote
         *   &servicehost=&serviceauth=&servicegroups=&wiki=&servicedesc=
         *   &router=api/test/nwtest/&returntype=JSON&method=POST&returnkeycode=id
         *   &returncodesucess=0&returnkeymsg=error&returnkeyresult=result
         *   &modelname=Quotation&ignore=1
         * @TODO heartbeat  心跳解决接口，用于检测服务状态
         */
        $parmas = [
            'serviceName' => $this->_request->getParam('servicename', ''),
            'serviceUrl' => $this->_request->getParam('serviceurl', ''),
            'serviceHost' => $this->_request->getParam('servicehost', ''),
            'serviceAuth' => $this->_request->getParam('isauth', 0),
            'serviceGroups' => $this->_request->getParam('servicegroups', 0),
            'wiki' => $this->_request->getParam('wiki', ''),
            'serviceDesc' => $this->_request->getParam('servicedesc', ''),
            'router' => $this->_request->getParam('router', ''),
            'returnType' => $this->_request->getParam('returntype', ''),
            'method' => strtoupper($this->_request->getParam('method', 'POST')),
            'returnKeyCode' => $this->_request->getParam('returnkeycode', 'false'),
            'returnCodeSucess' => $this->_request->getParam('returncodesucess', ''),
            'returnKeyMsg' => $this->_request->getParam('returnkeymsg', ''),
            'returnKeyResult' => $this->_request->getParam('returnkeyresult', ''),
            'modelName' => ucfirst(strtolower($this->_request->getParam('modelname', null))),
            'ignore' => $this->_request->getParam('ignore', 0)
        ];
        $registerModel = new \Rpc\RegisterModel();
        $resultModel = $registerModel->doHttp($parmas);
        $this->_changeToJson();
        $view = $this->getView();
        $view->errorcode = $resultModel->getResultCode();
        $view->errormsg = $resultModel->getResultMsg();
        $view->result = $resultModel->getResultData();
        $this->display();
    }
}