<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/31
 * Time: 15:06
 */
require_once dirname(__FILE__) . '/../html/index_header.php';
$application = new Yaf\Application(APPLICATION_PATH_CONFIG . "/application.ini", APPLICATION_ENV);
$application->bootstrap();
Api\Globals\Functions::lockProcess();
