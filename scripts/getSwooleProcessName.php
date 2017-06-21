<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/31
 * Time: 15:00
 */
require_once dirname(__FILE__) . '/index.php';
class SwooleProcessName
{
    private $_name;
    public function __construct($processName)
    {
        $config = new \Yaf\Config\Ini(dirname(__FILE__) . '/../conf/swoole.ini', APPLICATION_ENV);
        switch ($processName) {
            case \Api\Server\Http::PROCESS_NAME_MASTER:
            case \Api\Server\Http::PROCESS_NAME_WORKER:
            case \Api\Server\Http::PROCESS_NAME_TASK:
            case \Api\Server\Http::PROCESS_NAME_MANAGER:
                $this->_name = $config->swoole->processNamePre . $processName;
                break;
            case \Api\Server\Http::PROCESS_NAME_PRE:
                $this->_name = $config->swoole->processNamePre;
                break;
            default:
                $this->_name = $config->swoole->processNamePre . \Api\Server\Http::PROCESS_NAME_MASTER;
        }
    }

    public function getName()
    {
        return  $this->_name;
    }
}
$param = '';
if ($argc > 1) {
    $param = $argv[1];
}
$obj = new SwooleProcessName($param);
echo $obj->getName();