<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/6/05
 * Time: 20:44
 * @ from Yar编译包下的tool/yar_debug.ini
 */
/***************************************************************************
 *   基于swoole,必须安装swoole扩展
 *   用法：
 *   1. 按照正常yar调用方法，只是把类名修改为本调试类名。
 *   $yar = new \Swoole\Yar\Tcp('tcp://ip:port/path');
 *   var_dump($yar->call('method', $params));
 *   2. 支持tcp长连接
 *   $yar = new \Swoole\Yar\Tcp();
 *   $yar->connect(ip, port);
 *   $yar->setRouter($router);
 *          上面三步也可以简化为yar = new \Swoole\Yar\Tcp('tcp://ip:port/path');
 *   var_dump($yar->call('method', $params));
 *   如果需要访问其他的接口服务
 *   $yar->setRouter($router);
 *   var_dump($yar->call('method', $params));
 *
 *
 ***************************************************************************/
namespace Swoole\Yar;
require_once dirname(__FILE__) . '/Client.php';
class Tcp extends Client
{
    protected $_schema = 'tcp';
    protected $_host;
    protected $_port;
    protected $_currentRouter;
    protected $_query = '';
    protected $_async = false;
    /**
     * @var \Swoole\Client
     */
    protected $_swooleClient = null;
    public function __construct($url = null)
    {
        if ($url !== null) {
            $parseUrl = parse_url($url);
            if (!isset($parseUrl['scheme']) || !isset($parseUrl['host'])) {
                throw new \Exception('url格式错误,正确格式:[http|tcp]://host[:port][/path]', -1);
            }
            $this->_schema = $parseUrl['scheme'];
            $this->_host = gethostbyname($parseUrl['host']);
            $this->_port = isset($parseUrl['port']) ? $parseUrl['port'] : null;
            if (isset($parseUrl['path'])) {
                $this->_currentRouter = $parseUrl['path'];
            }
            if (isset($parseUrl['query'])) {
                $this->_query = $parseUrl['query'];
            }
        }
    }

    /**
     * @param $ip
     * @param $port
     * @return $this
     */
    public function connect($ip, $port, $timeout = 3)
    {
        $this->_host = gethostbyname($ip);
        $this->_port = $port;
        $this->_swooleClient = new \Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SYNC);
        $this->_swooleClient->set(
            [
                'open_eof_check' => true,
                'package_eof' => self::PACKAGE_EOF,
                'package_max_length' => 1024 * 1024 * 64,
                'socket_buffer_size' => 1024 * 1024 * 64
            ]
        );
        $this->_swooleClient->connect($this->_host, $this->_port, $timeout);
        return $this;
    }

    public function isConnected()
    {
        if ($this->_swooleClient === null)
            return false;
        return $this->_swooleClient->isConnected();
    }

    /**
     * @param $router
     *    tcp://ip:port/path 中的 /path部分
     *    $query url?uqery=1&key=2  ?后面部分
     */
    public function setRouter($router, $query = null)
    {
        $this->_currentRouter = '/' . ltrim($router, '/');
        $this->_query = $query;
    }

    protected function _transports($protocolPackeage)
    {
        if ($this->_currentRouter === null) {
            throw new \Exception('请先调用setRouter()', -3);
        } elseif ($this->_swooleClient === null) {
            $this->connect($this->_host, $this->_port, $this->_defaultTimeout);
        }
        $parseUrl = new \Swoole\Yar\Parseurl();
        $parseUrl->setHost($this->_host)
            ->setPort($this->_port)
            ->setPath($this->_currentRouter)
            ->setQuery($this->_query);
        $transportData = $this->_getTransportsPacket($parseUrl, $protocolPackeage);
        $this->_swooleClient->send($transportData);
        return Protocol::unpackServerData($this->_swooleClient->recv());
    }

    public function __destruct()
    {
        if (null != $this->_swooleClient) {
            $this->_swooleClient->close();
        }
    }
}