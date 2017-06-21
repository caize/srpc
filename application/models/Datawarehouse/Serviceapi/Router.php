<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/5/25
 * Time: 17:35
 */
namespace Datawarehouse\Serviceapi;
use \Illuminate\Database\Capsule\Manager as DB;
class RouterModel
{
    public function getRouterMap($router)
    {
        return DB::table('router_map')->join('api', 'router_map.apiid', '=', 'api.id')
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
            ->where('router_map.router', '=', $router)
            ->first();
    }
}