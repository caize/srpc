<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/5/2
 * Time: 15:18
 */
namespace Api\Server;
require_once __DIR__ . '/Aserver.php';
require_once __DIR__ . '/../Request/HttpSwoole2Yaf.php';
use Api\Globals\Functions;
use \Swoole\Http\Server;
use \Swoole\Http\Request;
use \Swoole\Http\Response;
use Yaf\Registry;
use Api\Request\HttpSwoole2Yaf;
class Http extends Aserver
{
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
        $http = new Server($config['bindip'], $config['bindport']);
        $http->set(
            array(
                'worker_num' => $config['workerNum'], #启动的worker进程,
                'daemonize' => $config['daemonize'],
                'max_request' => $config['maxRequest'], #只能用于同步模式
                'dispatch_mode' => $config['dispatchMode'],
                'log_file' => $config['logFile'],
                'buffer_output_size' => 16 * 1024 * 1024,
                'package_max_length' => 16 * 1024 * 1024,
            )
        );
        $http->on('Start', array($this, 'onStart'));
        $http->on('ManagerStart', array($this, 'onManagerStart'));
        $http->on('WorkerStart', array($this, 'onWorkerStart'));
        $http->on('request', array($this, 'onRequest'));
        $http->on('workerStop', array($this, 'onWorkerStop'));
        $http->addProcess($this->_createLoadDataProcessToSwooleTable($http));
        $http->start();
    }

    public function onRequest(Request $request, Response $response)
    {
        if ($request->server['path_info'] == '/favicon.ico'
            || $request->server['request_uri'] == '/favicon.ico'
        ) {
            return $response->end();
        }
        $startTime = microtime(true);
        // TODO handle img
        ob_start();
        Registry::set('swooleResponse', $response);
        Registry::set('swooleTable', $this->_swooleTable);
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
            $this->_application->getDispatcher()->dispatch($httpS2Y->getYafHttp());
            $this->_setRpcRequestLog('http', microtime(true) - $startTime);
            //$this->_application->getDispatcher()->setRequest($httpS2Y->getYafHttp());
            //$this->_application->bootstrap()->run();
        } catch (\Exception $e) {
            $data = array(
                'url' => $httpS2Y->getYafHttp()->getRequestUri(),
                'parmas' => http_build_query($this->_application->getDispatcher()->getRequest()->getParams()),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'traces' => $e->getTraceAsString(),
                'msg' => $e->getMessage(),
                'remoteIp' => Functions::getIpAddress(),
            );
            Registry::get('log')->put(
                $data,
                \Api\Log::ERROR
            );
            $error = array(
                'errorcode' => $e->getCode(),
                'errormsg' => $e->getMessage()
            );
            /**
             * 记录错误日志
             */
//            $mailProxy->setProjectName('mncgpg')
//                ->setSubject($subject)
//                ->setBodyText((string) $exception)
//                ->setFrom('mncgpg')
//                ->post();
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
        \Illuminate\Database\Capsule\Manager::connection('default')->disconnect();
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}