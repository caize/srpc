<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/6/5
 * Time: 15:12
 */
namespace Swoole\Yar;
require_once dirname(__FILE__) . '/Protocol.php';
require_once dirname(__FILE__) . '/Parseurl.php';
abstract class Client
{
    const PACKAGE_EOF = "\r\n=bc=\r\n";
    const YAR_OPT_TIMEOUT = 4;
    protected $_defaultTimeout = 3;
    protected function _getTransportsPacket(\Swoole\Yar\Parseurl $parurlObj, $protocolPackeage)
    {
        $host = $parurlObj->getHost();
        $path = $parurlObj->getPath() . (!empty($parurlObj->getQuery()) ? '?' . $parurlObj->getQuery() : '')
            . (!empty($parurlObj->getFragment()) ? '#' . $parurlObj->getFragment() : '');
        //$in = "\r\n";
        //增加结束标识
        $protocolPackeage['data'] = $protocolPackeage['data'];
        $in = "";
        $in .= "POST {$path} HTTP/1.1\r\n";
        $in .= "Host: {$host}\r\n";
        $in .= "Content-Type: application/octet-stream\r\n";
        $in .= "Connection: Close\r\n";
        $in .= "Hostname: {$host}\r\n";
        $in .= "Content-Length: " . strlen($protocolPackeage['data']) . "\r\n\r\n";
        return $in . $protocolPackeage['data'] . self::PACKAGE_EOF;
    }

    public function call($method, $arguments)
    {
        return $this->_transports(Protocol::Package($method, $arguments));
    }

    public function __call($method, $arguments)
    {
        return $this->_transports(Protocol::Package($method, $arguments));
    }

    /**
     * @param $key
     * @param $val ms
     */
    public function setOpt($key, $val)
    {
        if ($key == self::YAR_OPT_TIMEOUT) {
            $this->_defaultTimeout = intval($val/1000);
        }
    }
    abstract protected function _transports($protocolPackeage);
}