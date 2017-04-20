<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/27
 * Time: 11:03
 */
namespace Api\Iface;
interface Cache
{
    public function set($key, $val);

    public function get($key);

    public function del($key);

    public function expire($key, $expire);
}