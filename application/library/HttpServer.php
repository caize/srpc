<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/30
 * Time: 20:29
 */
use \Yaf\Config\Ini;
use \Swoole\Http\Server;
use \Swoole\Http\Request;
use \Swoole\Http\Response;
use \Yaf\Application;
use \Api\Request\HttpSwoole2Yaf;
use \Api\Router\Rewrite;
use \Api\Globals\Defined;
use \Yaf\Registry;
class HttpServer
{
    public static $instance;
    private $_application;
    protected $_swooleConfig = null;
    /**
     * @var Api\Iface\Log
     */
    protected $_workerLog = null;
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
        $http = new Server($config['bindip']['http'], $config['bindport']['http']);
        $http->set(
            array(
                'worker_num' => $config['workerNum'], #启动的worker进程,
                'daemonize' => $config['daemonize'],
                'max_request' => $config['maxRequest'], #只能用于同步模式
                'dispatch_mode' => $config['dispatchMode'],
                'log_file' => $config['logFile'],
            )
        );
        $http->on('Start', array($this, 'onStart'));
        $http->on('ManagerStart', array($this, 'onManagerStart'));
        $http->on('WorkerStart', array($this, 'onWorkerStart'));
        $http->on('request', array($this, 'onRequest'));
        $http->on('workerStop', array($this, 'onWorkerStop'));
        $http->start();
    }

    public function onStart(Server $server)
    {
        swoole_set_process_name($this->_swooleConfig->swoole->masterProcessName);
    }

    public function onManagerStart(Server $server)
    {
        swoole_set_process_name($this->_swooleConfig->swoole->managerProcessName);
    }

    public function onRequest(Request $request, Response $response)
    {
        if ($request->server['path_info'] == '/favicon.ico'
            || $request->server['request_uri'] == '/favicon.ico'
        ) {
            return $response->end();
        }
        // TODO handle img
        ob_start();
        Registry::set('swooleResponse', $response);
        /**
         * @data 20170413
         * 用于兼容swoole+yar 和 swoole+hprose
         */
        if (strtolower($request->server['request_method']) == 'post') {
            Registry::set(
                'systemServerProtocolRpcHeader',
                $request->rawContent()
            );
        }
        try {
            $httpS2Y = new HttpSwoole2Yaf($request);
            \Api\Globals\Functions::swooleHttpWriteLog($this->_workerLog, $httpS2Y->getYafHttp(), 'http');
            $this->_application->getDispatcher()->dispatch($httpS2Y->getYafHttp());
            //$this->_application->getDispatcher()->setRequest($httpS2Y->getYafHttp());
            //$this->_application->bootstrap()->run();
        } catch (\Exception $e) {
            Registry::get('log')->put(
                ' url ' . $httpS2Y->getYafHttp()->getRequestUri()
                . ' file:' . $e->getFile() . ' line:' . $e->getLine()
                . ' ' . $e->getMessage() . ' trace:' . $e->getTraceAsString(),
                \Api\Log::ERROR
            );
            $error = array(
                'errorcode' => $e->getCode(),
                'errormsg' => $e->getMessage()
            );
            echo json_encode($error);
        }
        $result = ob_get_contents();
        ob_end_clean();
        // add Header
        $response->header('Content-Type', 'text/html; charset=UTF-8');
        // add cookies
        foreach ($_COOKIE as $k => $val)
            $response->cookie($k, $val);
        // set status
        $response->end($result);
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
    public function onWorkerStart(Server $server, $workerId)
    {
        swoole_set_process_name($this->_swooleConfig->swoole->eventWorkerProcessName);
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

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new HttpServer;
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
        //ob_start();
        //$this->_application->bootstrap()->run();
        //$router = $this->_application->getDispatcher()->getRouter();
        //var_dump($router);
        //ob_end_clean();
    }
}