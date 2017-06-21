<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/28
 * Time: 18:57
 */
namespace Externalapi\Params;
use \Api\Iface\Params;
class QuoteModel implements Params
{
    public function getParams()
    {
        return array(
            'Method'        => '',
            'CodeList'      => '',
            'Code'          => '',
            'DataType'      => '',
            'DateTime'      => '',
            'FuncPeriod'=> '',
            'Fuquan'        => '',
            'Append'        => '',
            'formula'       => '',
            'DupCode'       => '',
            'SortType'      => '',
            'SortBy'        => '',
            'SortDir'        => '',
            'SortBegin'        => '',
            'SortCount'        => '',
            'SortAppend'        => '',
        );
    }
}