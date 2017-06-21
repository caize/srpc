<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 15:44
 */
namespace Admin;

use \Illuminate\Database\Capsule\Manager as DB;
use Common\ResultModel;

class RedisModel
{
    public function redisKeyNoExpire($appid = '', $keyPrex = '', $current = 1, $limit = 50)
    {
        $tableObj = DB::table('redis_cachekey')->where('expire', '>', date('Y-m-d H:i:s'))
            ->where('isvalid', '=', '1');
        if (!empty($appid)) {
            $tableObj->where('cachekey', 'like', $appid . '_' . $keyPrex . '%');
        }
        $tableObjCnt = $tableObj;
        $total = $tableObjCnt->count();
        $start = (($current > 0 ? $current : 1) - 1) * 50;
        $data = $tableObj->orderBy('expire')->offset($start)->limit($limit)->get()->toArray();
        $result['data'] = $data;
        $result['total'] = $total;
        $result['pageSize'] = $limit;
        $result['totalPage'] = ceil($total / $limit);
        return $result;
    }
}