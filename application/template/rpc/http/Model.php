<?php
/**
 * Created by System auto tools.
 * User: System;l.gang06@yahoo.com
 * Date: {DATE}
 * Time: {TIME}
 */
namespace Externalapi\Rpcapi;
use Rpc\RpchttpModel;
class {MODEL_NAME}Model extends RpchttpModel
{
    protected $_returnType = {RETURN_TYPE};

    protected function _errorCodeKeyMap()
    {
        return {KEY_ERROR_CODE};
    }

    protected function _errorCodeSuccessVal()
    {
        return {CODE_SUCCESS_VAL};
    }

    protected function _errorMsgKeyMap()
    {
        return '{KEY_ERROR_MSG}';
    }

    protected function _resultDataMap()
    {
        return '{KEY_DATA_DATA}';
    }
}