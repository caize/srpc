<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/23
 * Time: 16:58
 */
namespace Externalapi\Rpcapi;
use \Rpc\RpchttpModel;
class QuotationModel extends RpchttpModel
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
    public function send($params = array())
    {
        $data = parent::send($params);
        if ($this->_returnType == self::DATATYPE_XML) {
            $method = '';
            foreach ($params as $key => $v) {
                if ('method' == strtolower($key)) {
                    $method = $v;
                }
            }
            return $this->_parseXml($method, $data);
        } else {
            return $data;
        }
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

    protected function _parseXml($method, $result)
    {
        $desc = json_decode($result, true);
        if ($desc['errorcode'] != 0) {
            return $result;
        }
        switch (strtolower($method)) {
            case 'market':
                $quote = new \Quote\Result\Market();
                break;
            default:
                $quote = new \Quote\Result\Quote();
                break;
        }
        if (false === $quote->fromResult($desc['result']) || !$quote->isSucceed()) {
            $desc['errorcode'] = $quote->getError();
            $desc['errormsg'] = $quote->getErrorMessage();
            $desc['result'] = array();
        } else {
            $desc['result'] = $quote->getResult();
        }
        $desc['time'] = round(microtime(true) - $this->_startTime, 5);
        return json_encode($desc);
    }
}