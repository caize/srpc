<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/28
 * Time: 10:48
 * desc: rpc 继承控制器基础类
 */
namespace Api\Controller;
use Illuminate\Support\Facades\Response;
use \Yaf\Controller_Abstract;
class Rpc extends Controller_Abstract
{
    /**
     * @var \Api\Service\Rpc
     */
    protected $_rpcService;
    public function init()
    {
        $this->_rpcService = new \Api\Service\Rpc();
        $this->_rpcService->setResponse($this->_response);
    }
}