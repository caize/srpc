<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/5
 * Time: 16:21
 */
namespace Rpc;
use Yaf\Request\Http;
use Yaf\Registry;
use Api\Globals\Defined;
use Common\Result\HttpModel as ResultModel;
class RpclocalModel
{
    protected $_params = array();
    protected $_authToken = null;
    public function __set($name, $value)
    {
        $v = '_' . $name;
        $this->$v = $value;
    }
    /**
     * @param array $params
     *     对应http请求的参数
     *     有两种方式进行交互
     *      1. 直接绑定在请求url中如 http://api.10jqka.com.cn/api/test/test?test=1,
     *          这种方式 $params可以不穿
     *      2. 调用方法时传入，如 http://api.10jqka.com.cn/api/test/test,
     *          指定call方法时传入 array('test' => 1);
     *      注:如果同时传入http参数和call中的params参数，相同的key以params参数为准
     * @return json|xml
     */
    protected function _send($callBack, $params = array())
    {
        $result = new ResultModel();
        $startTime = microtime(true);
        try {
            $routerMap = RpcauthModel::getRouterMap($this->_authToken);
            $data = call_user_func_array($callBack, $params);
            $result->setResultData(json_encode($data));
        } catch (\Exception $e) {
            $result->setResultCode($e->getCode());
            $result->setResultMsg($e->getMessage());
        }
        $result->setResultTime(round(microtime(true) - $startTime, 5));
        return json_encode($result->getResult());
    }
}