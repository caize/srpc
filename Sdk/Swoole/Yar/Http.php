<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/6/05
 * Time: 20:44
 * @ from Yar编译包下的tool/yar_debug.ini
 */
/***************************************************************************
 *   用法：按照正常yar调用方法，只是把类名修改为本调试类名。
 *   1
 *   $yar = new \Swoole\Yar\Http('http://host:port/path');
 *   var_dump($yar->call('method', $params));
 *
 *
 ***************************************************************************/
namespace Swoole\Yar;
require_once dirname(__FILE__) . '/Client.php';
class Http extends Client
{
    protected $_url;
    public function __construct($url)
    {
        $this->_url = $url;
    }

    protected function _transports($protocolPackeage)
    {
        $parurlObj = new \Swoole\Yar\Parseurl($this->_url);
        $transPortsPack = $this->_getTransportsPacket($parurlObj, $protocolPackeage);

        $address = gethostbyname($parurlObj->getHost());
        $fp = fsockopen($address, $parurlObj->getPort(), $err, $errstr);
        if (!$fp) {
            die("cannot conncect to {$address} at port {$parurlObj->getPort()} '{$errstr}'");
        }
        stream_set_timeout($fp, $this->_defaultTimeout);
        fwrite($fp, $transPortsPack, strlen($transPortsPack));
        $fOut = '';
        $eofLen = strlen(self::PACKAGE_EOF);
        while (!feof($fp)) {
            $out = fgets($fp, 2086);
            if ($out == '') {
                break;
            }
            $fOut .= $out;
            if (substr($fOut, -$eofLen) === self::PACKAGE_EOF) {
                break;
            }
        }
        fclose($fp);
        return Protocol::unpackServerData($fOut);
    }
}