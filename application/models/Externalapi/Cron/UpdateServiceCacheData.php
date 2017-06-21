<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 9:59
 */
namespace Externalapi\Cron;

use \Illuminate\Database\Capsule\Manager as DB;
use Yaf\Registry;
class UpdateServiceCacheDataModel extends \BaseModel
{
    protected $_redisConfig = null;
    protected $_cacheManager = null;

    public function start()
    {
        $this->_redisConfig = Registry::get('redisConfig');
        $this->_cacheManager = Registry::get('cacheManager');
        $this->setRouterMap();
        $this->setResourceAuth();
        $this->setThirdAuth();
    }

    public function setRouterMap()
    {
        $rows = DB::table('router_map')->join('api', 'router_map.apiid', '=', 'api.id')
            ->select(
                'router_map.router',
                'api.id',
                'api.name',
                'api.url',
                'api.parameter',
                'api.isauth',
                'api.host',
                'api.status'
            )
            ->where('router_map.isvalid', '=', 1)
            //->where('api.status', '=', 1)
            ->get()->toArray();
        $routMapArr = array();
        foreach ($rows as $item) {
            $key = $item->router;
            $routMapArr[$key] = (array)$item;
            $key = str_replace('/', '_', $key);
            $this->_cacheManager->set($this->_redisConfig['api']['routermap'] . $key, json_encode($item));
            $this->_cacheManager->expire($this->_redisConfig['api']['routermap'] . $key, 86400);
        }
    }

    public function setResourceAuth()
    {
        $rows = DB::table('api')->join('auth_resource', 'api.id', '=', 'auth_resource.apiid')
            ->join('app', 'auth_resource.appid', '=', 'app.appid')
            ->select('auth_resource.appid', 'auth_resource.apiid')
            ->where("auth_resource.isvalid", '=', '1')
            ->where("api.status", '=', '1')->get();
        $cacheData = array();
        foreach ($rows as $item) {
            $cacheData[$item->appid][$item->apiid] = 1;
        }

        foreach ($cacheData as $appid => $items) {
            $cData = $this->_cacheManager->hGetAll($this->_redisConfig['api']['authresoucre'] . $appid);
            $delData = array_diff_key($cData, $items);
            if ($delData) {
                foreach ($delData as $apiid => $v) {
                    $this->_cacheManager->hDel($this->_redisConfig['api']['authresoucre'] . $appid, $apiid);
                }
            }
            $this->_cacheManager->hmSet($this->_redisConfig['api']['authresoucre'] . $appid, $items);
            $this->_cacheManager->expire($this->_redisConfig['api']['authresoucre'] . $appid, 1800);
        }
        return $cacheData;
    }

    public function setThirdAuth($appid = null)
    {
        $table = DB::table('auth_third_bind');
        if (!empty($appid)) {
            $table->where('appid', '=', $appid);
        }
        $rows = $table->get();
        $cacheData = array();
        foreach ($rows as $item) {
            $cacheData[$item->appid][$item->type] = $item->content;
        }

        foreach ($cacheData as $appid => $items) {
            $cData = $this->_cacheManager->hGetAll($this->_redisConfig['api']['auththirdbind'] . $appid);
            $delData = array_diff_key($cData, $items);
            if ($delData) {
                foreach ($delData as $type => $v) {
                    $this->_cacheManager->hDel($this->_redisConfig['api']['auththirdbind'] . $appid, $type);
                }
            }
            $this->_cacheManager->hmSet($this->_redisConfig['api']['auththirdbind'] . $appid, $items);
            $this->_cacheManager->expire($this->_redisConfig['api']['auththirdbind'] . $appid, 1800);
        }
        return $cacheData;
    }
}
