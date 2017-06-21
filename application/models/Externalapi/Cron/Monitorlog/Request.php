<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/5/8
 * Time: 15:21
 */
namespace Externalapi\Cron\Monitorlog;
use Common\ResultModel;
class RequestModel extends LogModel
{
    public function process($fileData)
    {
        $errorNum = count($fileData);
        $minTime = '';
        $maxTime = '';
        foreach ($fileData as $data) {
            $data1 = json_decode($data, true);
            if (!isset($data1['errorMsg']['time']) || $data1['errorMsg']['time'] < 1.5) {
                continue;
            }
            $this->_cacheManager->push(self::REDIS_KEY . self::REDIS_LIST_KEY, $data);
        }
    }
}