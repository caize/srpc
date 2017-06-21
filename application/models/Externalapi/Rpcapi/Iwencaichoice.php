<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/12
 * Time: 13:48
 */
namespace Externalapi\Rpcapi;
use Api\Client\Http;
use Api\Globals\Defined;
use Api\Globals\Functions;
use Rpc\RpchttpModel;
class IwencaichoiceModel extends RpchttpModel
{
    /**
     * @param array $params
     *      q 查询条件
     *      ret 返回类型  默认json
     */
    public function send($params = array())
    {
        $params = Functions::apiParamsCheck($params);
        if (!isset($params['ret'])) {
            $params['ret'] = 'json';
        } elseif ($params['ret'] == self::DATATYPE_XML) {
            $this->_returnType = self::DATATYPE_XML;
        }
        return parent::send($params);
    }

    protected function _errorCodeKeyMap()
    {
        return false;
    }

    protected function _errorCodeSuccessVal()
    {
        // TODO: Implement _errorCodeSuccessVal() method.
    }

    protected function _errorMsgKeyMap()
    {
        // TODO: Implement _errorMsgKeyMap() method.
    }

    protected function _resultDataMap()
    {
        // TODO: Implement _resultDataMap() method.
    }


}

