<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/23
 * Time: 16:58
 */
namespace Externalapi\Rpcapi;
use Rpc\RpchttpModel;
class IfinddataserviceModel extends RpchttpModel
{
    protected $_returnType = self::DATATYPE_PHP;

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