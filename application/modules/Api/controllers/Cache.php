<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/5/12
 * Time: 10:13
 */
use Api\Controller\Rpc;
class CacheController extends Rpc
{
    /**
     * redis缓存服务
     */
    public function redisAction()
    {
        $this->_rpcService->start(new \Localapi\Rpcapi\RedisModel());
    }

    /**
     * 用户内部临时存储缓存管理
     */
    public function appcachemanagerAction()
    {
        $this->_rpcService->start(new \Localapi\Rpcapi\AppCacheManagerModel());
    }
}