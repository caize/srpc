<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/31
 * Time: 15:06
 */
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
define('APPLICATION_PATH', dirname(__FILE__) . '/..');
define('APPLICATION_PATH_CONFIG', APPLICATION_PATH . '/conf');
$application = new Yaf\Application(APPLICATION_PATH_CONFIG . "/application.ini", APPLICATION_ENV);
$application->bootstrap();
Api\Globals\Functions::lockProcess();
