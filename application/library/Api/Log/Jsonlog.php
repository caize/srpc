<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/27
 * Time: 14:34
 */
namespace Api\Log;
use Api;
class Jsonlog extends Filelog
{
    protected function _format($msg, $level, &$date)
    {
        if ($level < $this->_levelLine) {
            return false;
        }
        $levelStr = self::$_levelStrArr[$level];
        $dateObj = new \DateTime();
        $date = $dateObj->format('Ymd');
        $log = $dateObj->format('Y-m-d H:i:s');
        $return = array(
            'datetime' => $log,
            'errorLevel' => $levelStr,
            'errorMsg' => $msg,
            'remoteIp' => Api\Globals\Functions::getIpAddress()
        );
        return json_encode($return) . "\n";
    }

}