<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/30
 * Time: 20:29
 */
namespace Api\Server;
use \Yaf\Config\Ini;
use \Swoole\Server;
use \Yaf\Application;
use \Yaf\Registry;
abstract class Aserver
{
    public static $instance;
    protected $_application;
    protected $_swooleConfig = null;
    /**
     * @var \Swoole\Table
     */
    protected $_swooleTable = null;
    const PROCESS_NAME_MASTER = 'master';
    const PROCESS_NAME_WORKER = 'worker';
    const PROCESS_NAME_TASK = 'task';
    const PROCESS_NAME_MANAGER = 'manager';
    const PROCESS_NAME_PRE = 'namepre';

    /**
     * @var Api\Iface\Log
     */
    protected $_workerLog = null;
    protected function __construct()
    {
        $this->_initSwooleTable();
        \Yaf\Registry::set('swooleTable', $this->_swooleTable);
    }

    public function setSwooleConfig(Ini $swooleConfig)
    {
        $this->_swooleConfig = $swooleConfig;
        return $this;
    }

    abstract public function start();

    public function onStart(Server $server)
    {
        $path = APPLICATION_PATH_APP_REPOSITORY . '/app';
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $appName = $path . '/appid';
        if (!file_exists($appName)) {
            file_put_contents($appName, uniqid('swoole', true));
        }
        swoole_set_process_name($this->_swooleConfig->swoole->processNamePre . self::PROCESS_NAME_MASTER);
    }

    public function onManagerStart(Server $server)
    {
        swoole_set_process_name($this->_swooleConfig->swoole->processNamePre . self::PROCESS_NAME_MANAGER);
    }

    /**
     * @param Server|null $serv
     */
    protected function _addWorkerLog(Server $serv = null)
    {
        $logConfigObj = new Ini(APPLICATION_PATH_CONFIG . '/log.ini', APPLICATION_ENV);
        $logConfig = $logConfigObj->req->toArray();
        $logType = ucfirst(strtolower($logConfig['logtype']));
        //config必须含有 timer值
        $className = "Api\\Log\\{$logType}";
        $this->_workerLog = new $className($logConfig);
        $this->_workerLog->registerTimer($logConfig['timer']);
    }
    public function onWorkerStart(Server $server, $workerId)
    {
        swoole_set_process_name($this->_swooleConfig->swoole->processNamePre . self::PROCESS_NAME_WORKER);
        ob_start();
        $this->_initApp();
        //增加定时器
        $this->_addWorkerLog($server);
        ob_end_clean();
    }

    public function onWorkerStop(Server $server, $workerId)
    {
        Registry::get('log')->flush();
    }

    public function onShutDown(Server $server)
    {
        Registry::get('log')->flush();
    }

    protected function _createLoadDataProcessToSwooleTable($serv)
    {
        if ($this->_swooleTable === null) {
            $this->_initSwooleTable();
        }
        $process = new \Swoole\Process(
            function ($process) use ($serv) {
                $this->_initApp();
                while (true) {
                    $data = \Illuminate\Database\Capsule\Manager::select(
                        "select id,appid from app where isvalid = 1"
                    );
                    foreach ($data as $row) {
                        if (!$this->_swooleTable->exist($row->appid)) {
                            $this->_swooleTable->set($row->appid, ['id' => $row->id]);
                           // $this->_swooleTable->set($row->appid, 'name', $row->name);
                          //  $this->_swooleTable->set($row->appid, 'name', $row->id);
                        }
                    }
                    \Illuminate\Database\Capsule\Manager::connection('default')->disconnect();
                    sleep(1800);
                }
            }
        );
        $process->name($this->_swooleConfig->swoole->processNamePre . 'Process');
        return $process;
    }

    private function _initSwooleTable()
    {
        if ($this->_swooleTable === null) {
            $this->_swooleTable = new \Swoole\Table(1024);
            $this->_swooleTable->column('id', \Swoole\Table::TYPE_INT);
           // $this->_swooleTable->column('name', \Swoole\Table::TYPE_STRING, 50);
          //  $this->_swooleTable->column('email', \Swoole\Table::TYPE_STRING, 50);
            $this->_swooleTable->create();
        }
    }

    private function _initApp()
    {
        if (null === $this->_application) {
            $this->_application = new Application(
                APPLICATION_PATH .
                "/conf/application.ini", APPLICATION_ENV
            );
            $this->_application->getDispatcher()->catchException(false);
            $this->_application->bootstrap();
            //设置定时器
            if (Registry::has('log')) {
                Registry::get('log')->registerTimer(5000);
            }
        }
        return $this;
        //ob_start();
        //$this->_application->bootstrap()->run();
        //$router = $this->_application->getDispatcher()->getRouter();
        //var_dump($router);
        //ob_end_clean();
    }

    protected function _setRpcRequestLog($serverType, $runTime)
    {
        $apiHttp = new \Api\Request\Http();
        $yafHttp = $this->_application->getDispatcher()->getRequest();
        $apiHttp->setRequestUri($yafHttp->getRequestUri());
        $apiHttp->setMethod($yafHttp->getMethod());
        if (Registry::has('rpcParams')) {
            $apiHttp->setParams(Registry::get('rpcParams'));
            Registry::del('rpcParams');
        } else {
            $apiHttp->setParams($yafHttp->getParams());
        }
        \Api\Globals\Functions::swooleHttpWriteLog(
            $this->_workerLog, $apiHttp, $serverType, $runTime
        );
    }
}