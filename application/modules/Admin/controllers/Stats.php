<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 15:26
 */
use Api\Controller\Base;
class StatsController extends Base
{
    public function rediskeyAction()
    {
        $model = new \Admin\RedisModel();
        $appid = $this->_request->getParam('appid', '');
        $key = $this->_request->getParam('key', '');
        $result = $model->redisKeyNoExpire(
            $appid,
            $key,
            $this->_request->getParam('page', 1)
        );
        $view = $this->getView();
        $view->list = $result;
        $view->appid = $appid;
        $view->key = $key;
        $view->currentPage = $this->_request->getParam('page', 1);

        $this->display();
    }
}
