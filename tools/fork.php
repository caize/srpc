<?php
require_once __DIR__ . '/../application/library/Syar/Tclient.php';
set_include_path(__DIR__ . '/../application/library' . PATH_SEPARATOR . get_include_path());
require_once __DIR__ . '/../application/library/Hprose/Autoload/Hprose.php';
require_once __DIR__ . '/../application/library/Hprose/Client.php';
require_once __DIR__ . '/../Sdk/Swoole/Yar/Tcp.php';
$logPath = '/tmp/fork_test.log';
file_put_contents($logPath, '');
$opt = getopt('c:n:l:t:m:p:x');
if (!isset($opt['c'])) {
    echo "please input -c [并发客户端数]\n";exit();
}

if (!isset($opt['n'])) {
    echo "please input -n [中请求]\n";exit();
}

if (!isset($opt['l'])) {
    echo "please input -l [地址]\n";exit();
}

if (!isset($opt['t'])) {
    echo "please input -t [curl|yar|hprose]\n";exit();
}
if (isset($opt['p'])) {
    $params = json_decode($opt['p'], true);
} else {
    $params = array();
}

if ($opt['t'] == 'yar' || $opt['t'] == 'hprose') {
    if (!isset($opt['m'])) {
        echo "please input -m [rpc call method]\n";exit();
    }
}
$url = $opt['l'];
//$url= "http://lg.api-swoole.10jqka.com.cn/api/test/dbtestrpc";
//$url="tcp://127.0.0.1:9502/api/test/dbtestrpc";
$type = $opt['t'];
//$type = 'yar';
$pidArr = array();
$mainS = microtime(true);
$avg = round($opt['n'] / $opt['c']);
$c = $opt['c'];

class Bf
{
    public  $url = null;
    public $type = null;
    public $method = null;
    public $params = null;
    protected $_scheme = null;
    public function __construct($url, $type, $method = null, $params = [])
    {
        $parseUrl = parse_url($url);
        $this->_scheme = $parseUrl['scheme'];
        if (!in_array($type, array('curl', 'yar', 'hprose'))) {
            $this->type = 'curl';
        } else {
            if ($this->_scheme == 'tcp' && $type == 'yar') {
                $this->type = 'yarTcp';
            } else {
                $this->type = $type;
            }
        }
        $this->url = $url;
        $this->method = $method;
        $this->params = $params;
    }

    public function run()
    {
        return call_user_func_array(array($this, $this->type), $this->params);
    }


    protected function curl()
    {
        $e = 1;
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        $no = curl_errno($ch);
        if ($no != 0 && $no{0} != '2') {
            $e = 0;
        }
        curl_close($ch);
        return $e;
    }

    protected function yar()
    {
        try {
            $new = new \Syar\Tclient($this->url);
            $data = $new->call($this->method, array($this->params));
            $arr = json_decode($data, true);
            if (!isset($arr['errorcode']) || $arr['errorcode'] != 0) {
                return false;
            }
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return 0;
        }
    }

    protected function yarTcp()
    {
        static $tcpObj = null;
        try {
            if ($tcpObj == null) {
                $tcpObj = new \Swoole\Yar\Tcp($this->url);
            }
            $data = $tcpObj->call($this->method, array($this->params));
            $arr = json_decode($data, true);
            if (!isset($arr['errorcode']) || $arr['errorcode'] != 0) {
                return false;
            }
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return 0;
        }
    }

    protected function hprose()
    {
        $client = \Hprose\Client::create(
            $this->url, false
        );
        call_user_func_array(array($client, $this->method), [$this->params]);
    }
}
$bf = new Bf($url, $type, $opt['m'], $params);
for ($i = 0; $i < $c; $i++) {
    $pid = pcntl_fork();
    $pidArr[] = $pid;
    if (!$pid) {
        $start = $startT = microtime(true);
        $min = $max = null;
        $error = 0;
        $returnArr = array(
            'error' => 0,
            'times' => array()
        );
        for ($j = 0; $j < $avg; $j++) {
			if ($j + 1 == $avg) {
				echo "$i;" . $avg . "\n";
			}
            if (!$bf->run()) {
                $error ++;
            }
            $tmpTime = microtime(true);
            $s = $tmpTime - $startT;
            $startT = $tmpTime;
            $returnArr['times'][] = $s;
        }
        foreach (array_chunk($returnArr['times'], 100) as $tk => $tItem) {
            if ($tk == 0) {
                $tmpReturn['error'] = $error;
            } else {
                $tmpReturn['error'] = 0;
            }
            $tmpReturn['times'] = $tItem;
            file_put_contents($logPath, json_encode($tmpReturn) . "\n", FILE_APPEND);
        }
        //$returnArr['error'] = $error;
        //file_put_contents($logPath, json_encode($returnArr) . "\n", FILE_APPEND);
        exit();
    }

}
while (count($pidArr) > 0) {
    foreach ($pidArr as $key => $pid) {
        $rs = pcntl_waitpid($pid, $status, WNOHANG);
        if ($rs == -1 || $rs > 0) {
            unset($pidArr[$key]);
        }
    }

}
$allTime = microtime(true) - $mainS;
$h = fopen($logPath, 'a+');
//$allTime = 0;
$min = 10000;
$max = 0;
$error = 0;
while (($row = fgets($h, 655535)) !== false) {
    $json = json_decode($row, true);
    foreach ($json['times'] as $time) {
//		$allTime += $time;
//var_dump($allTime .'=' .$time);
        if ($time < $min)
            $min = $time;
        if ($time > $max) {
            $max = $time;
        }
    }
    $error += $json['error'];
}
echo "allTtime:" . $allTime . "\n";
echo "all conntent:" . ($c * $avg) . "\n";
echo "client:" . $c . "\n";
echo "min time: " . $min . "\n";
echo 'max time:' . $max . "\n";
echo 'faild:' . $error . "\n";
echo "qps:" . floor($c * $avg / $allTime) . "\n";
fclose($h);
