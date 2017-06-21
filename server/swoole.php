<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/29
 * Time: 20:13
 */
require_once dirname(__FILE__) . '/../html/index_header.php';
$configIni = new \Yaf\Config\Ini(APPLICATION_PATH_CONFIG . '/swoole.ini', APPLICATION_ENV);
\Yaf\Loader::import(APPLICATION_PATH . '/application/library/Api/Server/Http.php');
define('SWOOLE_SERVER', 'HTTP');
\Api\Server\Http::getInstance()->setSwooleConfig($configIni)->start();