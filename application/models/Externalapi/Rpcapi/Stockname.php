<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/23
 * Time: 16:58
 */
namespace Externalapi\Rpcapi;
use Api\Globals\Functions;
use \Rpc\RpchttpModel;
class StocknameModel extends RpchttpModel
{
    protected $_returnType = self::DATATYPE_STRING;
    public function send($params)
    {
        if (isset($params['@returnType'])) {
            $this->_returnType = $params['@returnType'];
        }
        return parent::send(Functions::apiParamsCheck($params, array()));
    }

    public function lastName($fileName)
    {
        $params['@reqtype'] = 'lastname/' . $fileName;
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