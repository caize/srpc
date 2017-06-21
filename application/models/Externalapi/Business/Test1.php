<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/1
 * Time: 15:19
 */
namespace Externalapi\Business;
use \Illuminate\Database\Capsule\Manager as DB;
class Test1Model
{
    public function dbtest()
    {
        $data = DB::select('select SQL_NO_CACHE *,CURRENT_TIME from api');
        $result = new \Common\ResultModel();
        $result->setResultData($data);
        return json_encode($result->getResult());
    }
}