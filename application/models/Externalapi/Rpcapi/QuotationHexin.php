<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/23
 * Time: 16:58
 */
namespace Externalapi\Rpcapi;
use \Rpc\RpchttpModel;
class QuotationHexinModel extends RpchttpModel
{
    /**
     * @param array $params
     *     对应http请求的参数,参考Quote_Request_Quote::getParams
     *     有两种方式进行交互
     *      1. 直接绑定在请求url中如 http://api.10jqka.com.cn/api/quote/quote?test=1&auth-token=,
     *          这种方式 $params可以不穿
     *      2. 调用方法时传入，如 http://api.10jqka.com.cn/api/quote/quote?auth-token=,
     *          指定call方法时传入 array('test' => 1);
     *      注:如果同时传入http参数和call中的params参数，相同的key以params参数为准
     *
     *      参数参照 Quote_Request_Quote::getParams()
     *              Quote_Request_Market::getParams()
     *              Quote_Request_Order:getParams()
     * @return json|xml
     */
    public function send($params)
    {
        return parent::send($params);
    }

    protected function _errorCodeKeyMap()
    {
        return 'id';
    }

    protected function _errorCodeSuccessVal()
    {
        return 0;
    }

    protected function _errorMsgKeyMap()
    {
        return 'error';
    }

    protected function _resultDataMap()
    {
        return 'result';
    }


}