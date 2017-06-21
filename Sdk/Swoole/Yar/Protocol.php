<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/6/5
 * Time: 13:43
 */
namespace Swoole\Yar;
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

    public static function unpackServerData($data)
    {
        $tmp = explode("\r\n\r\n", $data, 2);
        if (isset($tmp[1])) {
            $returnData = unserialize(substr($tmp[1], 82 + 8));
        } elseif (empty($tmp)) {
            $returnData = false;
        } else {
            $returnData = unserialize(substr($tmp[0], 82 + 8));
        }
        if ($returnData === false) {
			var_dump($tmp);
            throw new \Exception('result data error:' . $data, -10);
        }
        if ($returnData['s'] == 0) {
            return $returnData['r'];
        } else {
            throw new \Exception($returnData['e'], $returnData['s']);
        }
    }
}
