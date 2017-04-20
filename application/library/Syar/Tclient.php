<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/18
 * Time: 20:44
 * @ from Yar编译包下的tool/yar_debug.ini
 */
/***************************************************************************
 *   用法：按照正常yar调用方法，只是把类名修改为本调试类名。
 *   1
 *   $yar = new Tclient('tcp://ip:port/path');
 *   var_dump($yar->call('method', $params));
        //这里通常能看到server端代码是否有错误，错误在那里。
 *   2
 *   Concurrent_Tclient::call('tcp://ip:port/path', 'method', $params, $callback, $errcallback);
 *   Concurrent_Tclient::loop();        //在callback也可以看到server反馈
 *
 *
 *   也可以用这个封装类做一个在线调试 exp:
 *   http://hk.yafdev.com/yar_server_response_viewer.php
 *
 *
 ***************************************************************************/
namespace Syar;
class Tclient
{
    private $_url;

    public function __construct($url)
    {
        $this->_url = $url;
    }

    public function call($method, $arguments)
    {
        return Transports::exec($this->_url, Protocol::Package($method, $arguments));
    }

    public function __call($method, $arguments)
    {
        return Transports::exec($this->_url, Protocol::Package($method, $arguments));
    }
}


class Concurrent_Tclient
{
    private static $_data = array();

    public static function call($uri, $m, $params = null, $callback = null, $errorcallback = null)
    {
        $package = Protocol::Package($m, $params);
        self::$_data[] = array(
            'uri' => $uri,
            'data' => $package,
            'callback' => $callback,
            'errcb' => $errorcallback,
        );
        return $package['transaction'];
    }

    public static function loop()
    {
        foreach (self::$_data as $v) {
            $ret = Transports::exec($v['uri'], $v['data']);
            if (strpos('HTTP/1.1 200 OK', $ret['header']) !== false) {
                $call = $v['callback'];
                $return = true;
            } else {
                $call = $v['errcb'];
                $return = false;
            }
            if (is_callable($call)) {
                $o = $ret['o'];
                $r = $ret['r'];
                call_user_func($call, $r, $o);
            }
        }
        return $return;
    }
}

class Protocol
{
    public static function Package($m, $params)
    {
        $struct = array(
            'i' => time(),
            'm' => $m,
            'p' => $params,
        );
        $body = str_pad('PHP', 8, chr(0)) . serialize($struct);

        $transaction = sprintf('%08x', mt_rand());

        $header = '';
        $header = $transaction;                        //transaction id
        $header .= sprintf('%04x', 0);                //protocl version
        $header .= '80DFEC60';                        //magic_num, default is: 0x80DFEC60
        $header .= sprintf('%08x', 0);                //reserved
        $header .= sprintf('%064x', 0);                //reqeust from who
        $header .= sprintf('%064x', 0);                //request token, used for authentication
        $header .= sprintf('%08x', strlen($body));    //request body len

        $data = '';
        for ($i = 0; $i < strlen($header); $i = $i + 2)
            $data .= chr(hexdec('0x' . $header[$i] . $header[$i + 1]));
        $data .= $body;
        return array(
            'transaction' => $transaction,
            'data' => $data
        );
    }
}

class Transports
{
    public static function exec($url, $data)
    {
        $urlinfo = parse_url($url);
        $port = isset($urlinfo["port"]) ? $urlinfo["port"] : 80;
        $path = $urlinfo['path'] . (!empty($urlinfo['query']) ? '?' . $urlinfo['query'] : '')
            . (!empty($urlinfo['fragment']) ? '#' . $urlinfo['fragment'] : '');

        $in = "POST {$path} HTTP/1.1\r\n";
        $in .= "Host: {$urlinfo['host']}\r\n";
        $in .= "Content-Type: application/octet-stream\r\n";
        $in .= "Connection: Close\r\n";
        $in .= "Hostname: {$urlinfo['host']}\r\n";
        $in .= "Content-Length: " . strlen($data['data']) . "\r\n\r\n";

        $address = gethostbyname($urlinfo['host']);

        $fp = fsockopen($address, $port, $err, $errstr);
        if (!$fp) {
            die("cannot conncect to {$address} at port {$port} '{$errstr}'");
        }
        fwrite($fp, $in . $data['data'], strlen($in . $data['data']));

        $fOut = '';
        while ($out = fread($fp, 2048))
            $fOut .= $out;

        $tmp = explode("\r\n\r\n", $fOut);
        if (isset($tmp[1])) {
            $returnData = unserialize(substr($tmp[1], 82 + 8));
        } elseif (empty($tmp)) {
            $returnData = false;
        } else {
            $returnData = unserialize(substr($tmp[0], 82 + 8));
        }
        fclose($fp);
        if ($returnData === false) {
            throw new \Yar_Client_Exception('result data error', -10);
        }
        if ($returnData['s'] == 0) {
            return $returnData['r'];
        } else {
            throw new \Yar_Client_Exception($returnData['e'], $returnData['s']);
        }
    }
}

