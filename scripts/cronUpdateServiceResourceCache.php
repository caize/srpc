<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 9:53
 */
require_once dirname(__FILE__) . '/index.php';
$cronMobel = new Externalapi\Cron\UpdateServiceCacheDataModel();
$cronMobel->start();
echo 'ok';
