<?php
/**
 * Created by l.gang06@yahoo.com
 * User: l.gang06@yahoo.com
 * Date: 2017/5/31
 * Time: 16:46
 */

namespace Log;

use \Illuminate\Database\Capsule\Manager as DB;
use Yaf\Plugin_Abstract;

class LogModel
{
    /**
     *
     * @param $type 1 访问次数 2 平均响应时间 3平均次数
     * @param $startDate
     * @param $endDate
     * @param $ip
     * @param $url
     */
    public function getServiceLogDetailByType($type, $startDate, $endDate, $ip, $url, $appid, $start, $size)
    {
        $condition = array();
        if ($type == 'error') {
            $condition['access'] = 0;
        } else {
            $condition['access'] = 1;
        }
        if (!empty($ip)) {
            $condition['remoteip'] = $ip;
        }
        if (!empty($url)) {
            $condition['url'] = $url;
        }
        if (!empty($appid)) {
            $condition['appid'] = $appid;
        }
        $res = DB::table('errorlog_service')
            ->whereBetween('datetime', [$startDate, $endDate])
            ->where($condition)
            ->orderBy('datetime', 'desc')
            ->skip($start)->take($size)
            ->get();
        $returnData = array();
        foreach ($res as $value) {
            $returnData[] = (array)$value;
        }
        return $returnData;
    }

    public function getAppLogDetailByType($startDate, $endDate, $start, $size)
    {
        $res = DB::table('errorlog_app')
            ->whereBetween('datetime', [$startDate, $endDate])
            ->orderBy('datetime', 'desc')
            ->skip($start)->take($size)
            ->get();
        $returnData = array();
        foreach ($res as $value) {
            $returnData[] = (array)$value;
        }
        return $returnData;

    }

    public function getAppLogTotalNumberByType($startDate, $endDate)
    {
        return DB::table('errorlog_app')
            ->whereBetween('datetime', [$startDate, $endDate])
            ->orderBy('datetime', 'desc')
            ->count();
    }

    public function getLogById($logId)
    {
        return (array)DB::table('errorlog_service')->where('id', $logId)->first();
    }

    /**
     * 根据条件查找日志信息
     * @param $startDate
     * @param $endDate
     * @param $ip
     * @param $url
     * @param $appid
     * @return mixed
     */
    public function query($startDate, $endDate, $ip, $url, $appid)
    {
        $condition = array();
        if (!empty($ip)) {
            $condition['remoteip'] = $ip;
        }
        if (!empty($url)) {
            $condition['url'] = $url;
        }
        if (!empty($appid)) {
            $condition['appid'] = $appid;
        }
        $totalCount = DB::table('errorlog_service')
            ->whereBetween('datetime', [$startDate, $endDate])
            ->where(['access' => 1])
            ->where($condition)
            ->count();
        $res['totalCount'] = $totalCount;
        $errorCount = DB::table('errorlog_service')
            ->whereBetween('datetime', [$startDate, $endDate])
            ->where('access', 0)
            ->where($condition)
            ->count();
        $res['errorCount'] = $errorCount;
        $avgTIme = DB::table('errorlog_service')
            ->whereBetween('datetime', [$startDate, $endDate])
            ->where(['access' => 1])
            ->where($condition)
            ->avg('time');
        $res['avgTime'] = round($avgTIme, 4);
        return $res;
    }

}