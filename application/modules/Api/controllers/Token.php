<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/6
 * Time: 13:14
 */
use Api\Controller\Base;
use Auth\TokenModel;
class TokenController extends Base
{
    public function getAction()
    {
        $this->_changeToJson();
        $object = new TokenModel();
        $result = $object->getToken(
            $this->_request->getParam('appid', null),
            $this->_request->getParam('secret', null)
        );
        $view = $this->getView();
        $view->errorcode = $result->getResultCode();
        $view->errormsg = $result->getResultMsg();
        $view->result = $result->getResultData();
        $this->display();
    }
}