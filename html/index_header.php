<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/10
 * Time: 10:47
 */
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
define('APPLICATION_PATH', dirname(__FILE__) . '/..');
define('APPLICATION_PATH_CONFIG', APPLICATION_PATH . '/conf');
define('APPLICATION_PATH_APP', APPLICATION_PATH . '/application');
set_include_path('/usr/local/lib/php/library' . PATH_SEPARATOR . get_include_path());