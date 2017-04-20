<?php
require_once __DIR__ . '/../application/library/Syar/Tclient.php';
$logPath = '/tmp/fork_test.log';
file_put_contents($logPath, '');
$url = "http://sapi-swoole.10jqka.com.cn/api/test/dbtest";
$url = 'http://sapi.10jqka.com.cn/api/test/hprose?&_lang=other';
//$url= "http://sapi-swoole.10jqka.com.cn/api/test/dbtestrpc";
//$url="tcp://127.0.0.1:9502/api/test/dbtestrpc";
$type = 'curl';
//$type = 'yar';
$pidArr = array();
$mainS = microtime(true);
$avg = 100;
$c = 100;
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
            if ($type == 'curl') {
                if (!curl($url)) {
                    $error++;
                }
            } else {
                if (!yar($url, 'dbtest')) {
                    $error++;
                }
            }
            $tmpTime = microtime(true);
            $s = $tmpTime - $startT;
            $startT = $tmpTime;
            $returnArr['times'][] = $s;
        }
        file_put_contents($logPath, json_encode($returnArr) . "\n", FILE_APPEND);
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
while (($row = fgets($h, 4096)) !== false) {
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
echo "url:" . $url . "\n";
echo "allTtime:" . $allTime . "\n";
echo "all conntent:" . ($c * $avg) . "\n";
echo "client:" . $c . "\n";
echo "min time: " . $min . "\n";
echo 'max time:' . $max . "\n";
echo 'faild:' . $error . "\n";
echo "qps:" . floor($c * $avg / $allTime) . "\n";
fclose($h);

function curl($url)
{
    $e = 1;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_exec($ch);
    $no = curl_errno($ch);
    if ($no != 0 && $no{0} != '2') {
        $e = 0;
    }
    curl_close($ch);
    return $e;

}

function yar($url, $method)
{
    try {
        $new = new \Syar\Tclient($url);
        $data = $new->call($method, array());
        return true;
    } catch (Exception $e) {
        echo $e->getMessage();
        return 0;
    }
}
