<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/31
 * Time: 17:25
 */
namespace Api;
use \Api\Cache\Redis;
class Cachemanager
{
    protected static $_cacheStroeArr = array();

    /**
     * @param $config
     * @return \Api\Cache\Cabstract
     */
    public static function getCache($config)
    {
        $type = isset($config['type']) ? $config['type'] : 'redis';
        if (!isset(self::$_cacheStroeArr[$type])) {
            switch ($type) {
                case 'redis':
                default :
                self::$_cacheStroeArr[$type] = new Redis($config);
            }
        }
        return self::$_cacheStroeArr[$type];
    }
}