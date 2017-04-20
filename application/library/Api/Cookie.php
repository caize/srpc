<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/20
 * Time: 10:38
 */
namespace Api;
use Yaf\Registry;

class Cookie
{
    public static $path = '/';
    public static $domain = null;
    public static $secure = false;
    public static $httponly = false;

    public static function get($key, $default = null)
    {
        if (!isset($_COOKIE[$key])) {
            return $default;
        }
        return $_COOKIE[$key];
    }

    public static function set($key, $val, $expire = 0)
    {
        if ($expire != 0)
            $expire = time() + $expire;
        if (defined('SWOOLE_SERVER')) {
            Registry::get('swooleResponse')->cookie(
                $key, $val, $expire, self::$path, self::$domain, self::$secure, self::$httponly
            );
        } else {
            setcookie($key, $val, $expire, self::$path, self::$domain, self::$secure, self::$httponly);
        }
    }

    public static function del($key)
    {
        self::set($key, '');
        if(isset($_COOKIE[$key]))
            unset($_COOKIE[$key]);
    }
}