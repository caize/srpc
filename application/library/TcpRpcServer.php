<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/30
 * Time: 20:29
 */
use \Yaf\Config\Ini;
use \Swoole\Server;
use \Yaf\Application;
use \Api\Router\Rewrite;
use \Api\Globals\Defined;
use \Yaf\Registry;
class TcpRpcServer
{
    public static $instance;
    private $_application;
    protected $_swooleConfig = null;

    private function __construct()
    {

    }

    public function setSwooleConfig(Ini $swooleConfig)
    {
         $this->_swooleConfig = $swooleConfig;
        return $this;
    }

    public function start()
    {
        if (null !== $this->_swooleConfig && isset($this->_swooleConfig->swoole)) {
            $config = $this->_swooleConfig->swoole->toArray();
        } else {
            $config = array();
        }
        if (empty($config['bindip']) || empty($config['bindport'])) {
            throw new \Exception('swoole config error');
        }
        $serv = new Server($config['bindip']['tcp'], $config['bindport']['tcp']);
        $serv->set(
            array(
                'worker_num' => $config['workerNum'], #启动的worker进程,
                'daemonize' => $config['daemonize'],
                'max_request' => $config['maxRequest'], #只能用于同步模式
                'dispatch_mode' => $config['dispatchMode'],
                'log_file' => $config['logFile'],
            )
        );
        $serv->on('Start', array($this, 'onStart'));
        $serv->on('ManagerStart', array($this, 'onManagerStart'));
        $serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $serv->on('workerStop', array($this, 'onWorkerStop'));

        //增加tcp事件
        $serv->on('connect', array($this, 'onConnect'));
        $serv->on('receive', array($this, 'onReceive'));
        $serv->on('Close', array($this, 'onClose'));
        $serv->start();
    }

    public function onConnect($serv, $fd, $reactorId)
    {
        //echo 'onConncet:'. $fd;
        //可以记录连接日志
        //var_dump($fd, $reactorId);
    }

    public function onClose($serv, $fd, $reactorId)
    {
        //var_dump($fd, $reactorId);
        //可以记录连接日志
    }

    public function onReceive($serv, $fd, $fromId, $data)
    {
        $yarData = explode("\r\n\r\n", $data);
        $cnt = count($yarData);
        /**
         * @20170418
         * 暂时只支持yar 必须使用 Syar/Tclient 客户端
         */
        if (isset($yarData[1]) && $cnt == 2) {
            ob_start();
            $yarHeaderInfo = explode("\r\n", $yarData[0]);
            //yar
            $httpInfo = explode(" ", $yarHeaderInfo[0]);
            $urlInfo = parse_url($httpInfo[1]);
            $http = new \Yaf\Request\Http();
            $http->method = $httpInfo[0];
            $http->setRequestUri($urlInfo['path']);
            if (isset($urlInfo['query'])) {
                $urlInfo['query'] = trim($urlInfo['query'], '&');
                $params = explode('&', $urlInfo['query']);
                foreach ($params as $k => $val) {
                    $t = explode('=', $val);
                    $http->setParam($t[0], $t[1]);
                }
            }
            if (strtolower($http->method) == 'post') {
                Registry::set(
                    'systemServerProtocolRpcHeader',
                    $yarData[1]
                );
            }
            \Api\Globals\Functions::swooleHttpWriteLog($this->_workerLog, $http, 'tcp');
            $rs = $this->_application->getDispatcher()->dispatch($http);
            $result = ob_get_contents();
            //设置 header；
            $header = array(
                'Date: ' . date('Y-m-d H:i:s'),
                'Content-Type: text/html; charset=UTF-8',
                'Content-Length: ' . strlen($result)
            );
            $result = implode("\r\n", $header) . "\r\n\r\n" . $result;
            ob_end_clean();
            $serv->send($fd, $result);
        } else {
            $serv->send($fd, json_encode(array('errorcode' => -1, 'errormsg'=>'error')));
        }

//
//        $new->handle();
        $serv->close($fd);
    }

    public function onStart(Server $server)
    {
        swoole_set_process_name($this->_swooleConfig->swoole->masterProcessName);
    }

    public function onManagerStart(Server $server)
    {
        swoole_set_process_name($this->_swooleConfig->swoole->managerProcessName);
    }
    /**
     * @param Server|null $serv
     */
    protected function _addWorkerLog(Server $serv = null)
    {
        $logConfigObj = new Yaf\Config\Ini(APPLICATION_PATH_CONFIG . '/log.ini', APPLICATION_ENV);
        $logConfig = $logConfigObj->req->toArray();
        //config必须含有 timer值
        $className = "Api\\Log\\{$logConfig['logtype']}";
        $this->_workerLog = new $className($logConfig);
    }

    public function onWorkerStart(Server $server)
    {
        swoole_set_process_name($this->_swooleConfig->swoole->eventWorkerProcessName);
        ob_start();
        $this->_initApp();
        $this->_addWorkerLog($server);
        ob_end_clean();
    }

    public function onWorkerStop(Server $server, $workerId)
    {
    }

    public function onShutDown(Server $server)
    {
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
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
            //重设 projectBaseUrl;
            $objConfig = Registry::get('config');
            Registry::set('projectBaseUrl', $objConfig['application']['baseUrl_swoole']);
        }
        return $this;
    }

    public function test()
    {
        return 'time:' . time();
    }
}
