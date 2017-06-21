<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/5/2
 * Time: 15:18
 */
namespace Api\Server;
require_once __DIR__ . '/Aserver.php';
use Api\Globals\Defined;
use \Swoole\Server;
use Syar\Protocol;
use Yaf\Registry;
class Tcp extends Aserver
{
    const PACKAGE_EOF = "\r\n=bc=\r\n";
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
        $serv = new Server($config['bindip'], $config['bindport']);
        $serv->set(
            array(
                'worker_num' => $config['workerNum'], #启动的worker进程,
                'daemonize' => $config['daemonize'],
                'max_request' => $config['maxRequest'], #只能用于同步模式
                'dispatch_mode' => $config['dispatchMode'],
                'log_file' => $config['logFile'],
                'buffer_output_size' => 32 * 1024 * 1024,
                'package_max_length' => 32 * 1024 * 1024,
                'heartbeat_check_interval' => $config['heartbeatcheckinterval'],
                'heartbeat_idle_time' => $config['heartbeatidletime'],
                'task_worker_num' => $config['tasknum'],
                'open_eof_check' => true,
                'package_eof' => self::PACKAGE_EOF,
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
        $serv->on('Task', [$this, 'onTask']);
        $serv->on('Finish', [$this, 'onFinish']);
        $serv->addProcess($this->_createLoadDataProcessToSwooleTable($serv));
        $serv->start();
    }

    public function onConnect($serv, $fd, $reactorId)
    {
        //print_r($serv->connection_info($fd));
        //echo 'onConncet:'. $fd;
        //可以记录连接日志
        //var_dump($fd, $reactorId);
    }

    public function onClose($serv, $fd, $reactorId)
    {
        //var_dump($fd, $reactorId);
        //可以记录连接日志
    }

    public function onTask($serv, $taskId, $fromId, $data)
    {
        $dataDesc = unserialize($data);
        unset($data);
        $startTime = microtime(true);
        $fd = $dataDesc['fd'];
        $connectionInfo = $serv->connection_info($fd);
        $_SERVER['REMOTE_ADDR'] = $connectionInfo['remote_ip'];
        $yarData = explode("\r\n\r\n", $dataDesc['data'], 2);
        unset($dataDesc);
        $cnt = count($yarData);
        /**
         * @20170418
         * 暂时只支持yar 必须使用 Syar/Tclient 客户端
         */
        if (isset($yarData[1]) && $cnt == 2) {
            ob_start();
            $yarHeaderInfo = explode("\r\n", $yarData[0]);
            //yar
            $httpInfo = array();
            foreach ($yarHeaderInfo as $hInfo) {
                if (strpos($hInfo, 'POST') === 0) {
                    $httpInfo = explode(" ", $hInfo);
                    break;
                }
            }
            unset($yarHeaderInfo);
            try {
                if (empty($httpInfo)) {
                    throw new \Exception(Defined::MSG_TCP_PARSE_FAILED_DATA, Defined::CODE_TCP_PARSE_FAILED_DATA);
                }
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
                $rs = $this->_application->getDispatcher()->dispatch($http);
                $result = ob_get_contents();
            } catch (\Exception $e) {
                $pack = new\Syar\Pack();
                $yar = $pack->unpack($yarData[1]);
                $yar->setError($e->getMessage(), $e->getCode());
                $result = $pack->pack($yar);
                $result = ob_get_contents();
            }
            //设置 header；
            $header = array(
                'Date: ' . date('Y-m-d H:i:s'),
                'Content-Type: text/html; charset=UTF-8',
                'Content-Length: ' . strlen($result)
            );
            $result = implode("\r\n", $header) . "\r\n\r\n" . $result . self::PACKAGE_EOF;
            ob_end_clean();
            $serv->send($fd, $result);
            $this->_setRpcRequestLog('tcp', microtime(true) - $startTime);
        } else {
            //   ob_start();
            //yar
            $serv->send($fd, json_encode(array('errorcode' => -1, 'errormsg' => 'error')) . self::PACKAGE_EOF);
        }
        \Illuminate\Database\Capsule\Manager::connection('default')->disconnect();
        //$serv->finish();
    }

    public function onFinish($serv, $taskId, $data)
    {
    }

    public function onReceive(\Swoole\Server $serv, $fd, $fromId, $data)
    {
        //处理结尾标识
        $dataLen = strlen($data);
        $packeofLen = strlen(self::PACKAGE_EOF);
        if (substr($data, -$packeofLen) == self::PACKAGE_EOF) {
            $data = substr($data, 0, $dataLen - $packeofLen);
        }
        $serialize = serialize(['fd' => $fd, 'data' => $data]);
        $taskId = $serv->task($serialize);
        return ;
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}