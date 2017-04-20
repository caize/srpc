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
            case 'worker':
                $this->_name = $config->swoole->eventWorkerProcessName;
                break;
            case 'task':
                $this->_name = $config->swoole->eventTaskProcessName;
                break;
            case 'manager':
                $this->_name = $config->swoole->managerProcessName;
                break;
            case 'master':
            default:
                $this->_name = $config->swoole->masterProcessName;
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