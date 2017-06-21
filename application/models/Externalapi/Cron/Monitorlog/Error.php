<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/5/8
 * Time: 16:39
 */
namespace Externalapi\Cron\Monitorlog;
class ErrorModel extends LogModel
{
    public function process($fileData)
    {
        $errorNum = count($fileData);
        $minTime = '';
        $maxTime = '';
        foreach ($fileData as $data) {
            $desc = json_decode($data, true);
            if (!isset($desc['errorLevel']) ||
                !($desc['errorLevel'] == 'ERROR' || $desc['errorLevel'] == 'WARN')) {
                continue;
            }
//            if ($desc['datetime'] > $minTime) {
//                $minTime = $desc['datetime'];
//            }
//            if ($desc['datetime'] > $maxTime) {
//                $maxTime = $desc['datetime'];
//            }
            $this->_cacheManager->push(self::REDIS_KEY . self::REDIS_LIST_KEY, $data);
        }

//        if ($errorNum > 10) {
//            $fileData = array_slice($fileData, $errorNum - 11, 10);
//        }
//        var_dump(implode("\r\n", $fileData));
//        $mailProxy	= new Hexin\MailProxy();
//        $subject = '紧急：rpc' . (string)$jobname . '执行异常，请尽快修复';
//        if (!$error) {
//            $subject = $jobname . '执行完成';
//        }
//        $mailProxy->setProjectName('mncg_yunwei')
//            ->setSubject($subject)
//            ->setBodyText((string) $exception)
//            ->setFrom('mncg_yunwei')
//            ->post();
    }

    public function notice()
    {
        $noticeData = array();
        $startTime = time();
        while (true) {
            if ((time() - $startTime) >= 60) {
                $startTime = time();
                if (!empty($noticeData)) {
                    $subject = 'rpc平台错误';
                }
            }
            $data = $this->_cacheManager->pop(self::REDIS_KEY . $this->_storageKey);
            if (empty($data)) {
                sleep(1);
                continue;
            }
            $noticeData[] = $data;
        }
    }
}