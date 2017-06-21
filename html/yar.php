<?php
$client = new Yar_Client('http://rpc.10jqka.com.cn/api/iwencai/openapi');
$data = $client->call('searchSpell', array(array('ret' => 'json', 'q'=> '300033', 'spt'=>'', 'dto'=>'', '@appid' => '58eae00591d05')));
var_dump($data);exit();
$client->test();
