<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/29
 * Time: 20:13
 */

require_once dirname(__FILE__) . '/../html/index_header.php';
$configIni = new \Yaf\Config\Ini(APPLICATION_PATH_CONFIG . '/swooleTcp.ini', APPLICATION_ENV);
\Yaf\Loader::import(APPLICATION_PATH . '/application/library/Api/Server/Tcp.php');
define('SWOOLE_SERVER', 'swoole');
\Api\Server\Tcp::getInstance()->setSwooleConfig($configIni)->start();