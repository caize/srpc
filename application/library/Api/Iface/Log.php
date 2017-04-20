<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/27
 * Time: 11:28
 */
namespace Api\Iface;
use Api;
interface Log
{
    public function put($msg, $level = Api\Log::NOTICE);
    public function flush();
}