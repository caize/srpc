<?php

/*
*Created by l.gang06@yahoo.com
* User: l.gang06@yahoo.com
* Date: 2017/5/22 * Time: 14:52
*/

class UnittestController extends \Api\Controller\Base
{
    public function apihelperAction()
    {
        $url = $this->getRequest()->getParam('url', null);
        $method = $this->getRequest()->getParam('method', null);
        $data = $this->getRequest()->getParam('data', null);
        $tempArray = explode('.', $data);
        $length = count($tempArray);
        $realData = array();
        for ($i = 0; $i < $length - 1; $i += 2) {
            $realData[] = [$tempArray[$i] => $tempArray[$i + 1]];
        }
        $connectTimeOut = $this->getRequest()->getParam('connectTimeOut', 1000);
        $timeOut = $this->getRequest()->getParam('timeOut', 6000);
        $yarClient = new Yar_Client($url);
        $yarClient->setOpt(YAR_OPT_CONNECT_TIMEOUT, $connectTimeOut);
        $yarClient->setOpt(YAR_OPT_TIMEOUT, $timeOut);
        echo $yarClient->call($method, $realData);
    }

    public function arrayapihelperAction()
    {
        $url = $this->getRequest()->getParam('url', null);
        $method = $this->getRequest()->getParam('method', null);
        $data = $this->getRequest()->getParam('data', null);
        $dataArray = explode('.', $data);
        $length = count($dataArray);
        $realData = array();
        for ($i = 0; $i < $length - 1; $i += 2) {
            $realData[$dataArray[$i]] = $dataArray[$i + 1];
        }
        $data = array($realData);
        $connectTimeOut = $this->getRequest()->getParam('connectTimeOut', 1000);
        $timeOut = $this->getRequest()->getParam('timeOut', 6000);
        $yarClient = new Yar_Client($url);
        $yarClient->setOpt(YAR_OPT_CONNECT_TIMEOUT, $connectTimeOut);
        $yarClient->setOpt(YAR_OPT_TIMEOUT, $timeOut);
        echo $yarClient->call($method, $data);
    }

    public function newarrayapihelperAction()
    {
        $url = $this->getRequest()->getParam('url', null);
        $method = $this->getRequest()->getParam('method', null);
        $data = json_decode($this->getRequest()->getParam('data', null), true);
        $tempArray = array();
        foreach ($data as $key => $value) {
            $tempArray[$key] = $value;
        }
        $realData[] = $tempArray;
        $connectTimeOut = $this->getRequest()->getParam('connectTimeOut', 1000);
        $timeOut = $this->getRequest()->getParam('timeOut', 6000);
        $yarClient = new Yar_Client($url);
        $yarClient->setOpt(YAR_OPT_CONNECT_TIMEOUT, $connectTimeOut);
        $yarClient->setOpt(YAR_OPT_TIMEOUT, $timeOut);
        echo $yarClient->call($method, $realData);
    }

    public function testgetAction()
    {
        $url = 'http://rpc.myhexin.com/api/cache/redis';
        $yarClient = new Yar_Client($url);
        $yarClient->setOpt(YAR_OPT_CONNECT_TIMEOUT, 1000);
        $yarClient->setOpt(YAR_OPT_TIMEOUT, 6000);
        echo $yarClient->call('get', array('cwjapp', 'testname'));
    }

    public function getYarClient()
    {
        $url = 'http://rpc.myhexin.com/api/cache/redis';
        $yarClient = new Yar_Client($url);
        $yarClient->setOpt(YAR_OPT_CONNECT_TIMEOUT, 1000);
        $yarClient->setOpt(YAR_OPT_TIMEOUT, 6000);
        return $yarClient;
    }

    public function testsetAction()
    {
        $yarClient = $this->getYarClient();
        echo $yarClient->call('set', ['cwjapp', 'testname', 'cwj', 3600]);
    }

    public function testdelAction()
    {
        echo $this->getYarClient()->call('del', ['cwjapp', 'testname']);
    }

    public function testmsetAction()
    {
        $keyVals = [
            'testmsetkey1' => 'testmsetvalue1',
            'testmsetkey2' => 'testmsetvalue2'
        ];
        echo $this->getYarClient()->call('mSet', ['cwjapp', $keyVals, 3600]);
    }

    public function testmgetAction()
    {
        $keyArray = ['testmsetkey1', 'testmsetkey2'];
        echo $this->getYarClient()->call('mGet', ['cwjapp', $keyArray]);
    }

    public function testpushAction()
    {
        $vals = ['cwj1', 'cwj2', 'cwj3'];
        echo $this->getYarClient()->call('push', ['cwjapp', 'testpushkeyname', $vals]);
    }

    public function testpopAction()
    {
        $num = 99;
        echo $this->getYarClient()->call('pop', ['cwjapp', 'testpushkeyname', $num]);
    }

    public function testhsetAction()
    {
        echo $this->getYarClient()->call('hSet', ['cwjapp', 'testhsetkey', 'testhsetrealkey', 'hsetvalue', 3600]);
    }

    public function testhgetAction()
    {
        echo $this->getYarClient()->call('hGet', ['cwjapp', 'testhsetkey', 'testhsetrealkey']);
    }

    public function testhmsetAction()
    {
        $keyvals = [
            'hmsetkey1' => ['testhmsetval11', 'testhmsetval12'],
            'testhmsetkey2' => 'testhmsetval2'];
        echo $this->getYarClient()->call('hMSet', ['cwjapp', 'testhsetkey', $keyvals, 3600]);
    }

    public function testhmgetAction()
    {
        $keys = ['testhmsetkey1', 'testhmsetkey2'];
        echo $this->getYarClient()->call('hMGet', ['cwjapp', 'testhsetkey', $keys]);
    }

    public function testhdelAction()
    {
        echo $this->getYarClient()->call('hDel', ['cwjapp', 'testhsetkey', 'testhmsetkey1']);
    }

    public function testhgetallAction()
    {
        echo $this->getYarClient()->call('hGetAll', ['cwjapp', 'testhsetkey']);
    }

    public function testformulacachegetdatasAction()
    {
        $yarClient = new Yar_client('http://rpc.myhexin.com/api/quote/formulacache');
        $yarClient->setOpt(YAR_OPT_CONNECT_TIMEOUT, 1000);
        $yarClient->setOpt(YAR_OPT_TIMEOUT, 6000);
        $param1 = [1 => '300033', '2' => '600000'];
        $param2 = [1 => '11', 2 => '367250', 3 => '10'];
        echo $yarClient->call('getDatas', [$param1, $param2]);
    }

    public function testopenapigraphconceptrelationAction()
    {
        $url = 'http://rpc.myhexin.com/api/iwencai/openapi';
        $yarClient = new Yar_Client($url);
        $yarClient->setOpt(YAR_OPT_CONNECT_TIMEOUT, 1000);
        $yarClient->setOpt(YAR_OPT_TIMEOUT, 6000);
        $queryData = [
            'concept_name' => '央企国资改革',
            'begin_time' => '20100101',
            'end_time' => '30150101',
            'appid' => '4549152Eb776',
            'secret' => 'efa9e9209a6efd7e0b47d5992baa4421'];
        echo $yarClient->call('graphConceptRelation', $queryData);
    }

    public function testbatchdelcacheAction()
    {
        echo $this->getYarClient()->call('batchDelCache', ['cwjapp', 'testbatch', 'try']);
    }

    /**
     * 推送通道-用户个性化设置-send
     */
    public function testuserselfdomsendAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/push/individualization?@appid=58eae00591d05&&auth-token=");
        echo $yarClient->call("send", array(0 => array('op' => 'searchStock ', 'stock' => '300033')));
    }

    /**
     * 推送通道-用户个性化设置-deleteStock
     */
    public function testuserselfdomdeletestockAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/push/subscriptioncolumn?@appid=58eae00591d05&&auth-token=");
        echo $yarClient->call("unsubscribe", array(0 => array('userid' => '1', 'cate' => '1', 'type' => 'add')));
    }

    /**
     * 推送通道-用户个性化设置-getfocus ,#未定义
     */
    public function testuserselfdomgetfocusAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/push/individualization?@appid=58eae00591d05&&auth-token=");
        echo $yarClient->call("getFocusStock", array(0 => array('userid' => '1')));
    }

    /**
     * 推送通道-用户个性化设置-addstock ,#未定义
     */
    public function testuserselfdomaddstockAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/push/individualization?@appid=58eae00591d05&&auth-token=");
        echo $yarClient->call("addStock", array(0 => array('userid' => '1', 'stockInfo' => '300033')));
    }

    /**
     * 推送通道-用户个性化设置-savefocusstock ,#未定义
     */
    public function testuserselfdomsavefocusstockAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/push/individualization?@appid=58eae00591d05&&auth-token=");
        echo $yarClient->call("saveFocusStock", array(0 => array('userid' => '1', 'stockInfo' => '["300033"]')));
    }

    /**
     * 推送通道-用户个性化设置-searchstock ,#未定义
     */
    public function testuserselfdomsearchstockAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/push/individualization?@appid=58eae00591d05&&auth-token=");
        echo $yarClient->call("searchStock", array(0 => array('userid' => '1', 'stock' => '300033')));
    }

    /**
     * 推送通道-用户订阅-send ,#查询 check
     */
    public function testusersubcribesendcheckAction()
    {
        $url = 'http://rpc.myhexin.com/api/push/subscriptioncolumn?@appid=58eae00591d05&&auth-token=';
        $yarClient = new Yar_Client($url);
        $params = ['mod' => 'td', '@reqtype' => '/interface/main.php', 'userid' => '1'];
        echo $yarClient->call("send", array(0 => array($params)));
    }

    /**
     * 推送通道-用户订阅-send ,#订阅 add
     */
    public function testusersubcribesendaddAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/push/subscriptioncolumn?@appid=58eae00591d05&&auth-token=");
        $params = ['userid' => '1', '@reqtype' => 'td/', 'type' => 'add', 'cate' => '1'];
        echo $yarClient->call("send", array(0 => array($params)));
    }

    /**
     * 推送通道-用户订阅-send ,#退订 del
     */
    public function testusersubcribesenddelAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/push/subscriptioncolumn?@appid=58eae00591d05&&auth-token=");
        $params = ['userid' => '1', '@reqtype' => 'td/', 'type' => 'del', 'cate' => '1'];
        echo $yarClient->call("send", array(0 => array($params)));
    }

    /**
     * 推送通道-用户订阅-uninfo ,#用户退订订阅信息
     */
    public function testusersubcribeuninfoAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/push/subscriptioncolumn?@appid=58eae00591d05&&auth-token=");
        echo $yarClient->call("uninfo", array(0 => array('userid' => '1')));
    }

    /**
     * 推送通道-用户订阅-columnslist ,#未定义
     */
    public function testusersubcribecolumnslistAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/push/subscriptioncolumn?@appid=58eae00591d05&&auth-token=");
        echo $yarClient->call("columnsList", array(0 => array('appid' => '2')));
    }

    /**
     * 推送通道-用户订阅-unsubcribe ,#del
     */
    public function testusersubcribeunsubcribedelAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/push/subscriptioncolumn?@appid=58eae00591d05&&auth-token=");
        echo $yarClient->call("unsubscribe", array(0 => array('userid' => '1', 'cate' => '1', 'type' => 'add')));
    }

    /**
     * 推送通道-用户订阅-unsubcribe ,#del
     */
    public function testusersubcribeunsubcribeaddAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/push/subscriptioncolumn?@appid=58eae00591d05&&auth-token=");
        echo $yarClient->call("unsubscribe", array(0 => array('userid' => '1', 'cate' => '1', 'type' => 'del')));
    }

    /**
     * 行情服务 quote 12-hq
     */
    public function testquote12hqAction()
    {
        $yarClient = new Yar_Client("http://rpc.myhexin.com/api/quote/quote?@appid=58eae00591d05&&auth-token=");
        $params = ['Method' => 'Quote', 'Fuquan' => 'Q',
            'CodeList' => '33(300635)', 'DataType' => '11;367250', 'DateTime' => '16384(20170331-20170331)'];
        echo $yarClient->call("send", array(0 => array($params)));
    }

    /**
     *  行情服务 hexin-free
     */
    public function testhexinfreeAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/quote/hexinfree?@appid=58eae00591d05&&auth-token=");
        $params = ['Method' => 'Quote', 'Fuquan' => 'Q', 'CodeList' => '33(300635)',
            'DataType' => '11;367250', 'DateTime' => '16384(20170331-20170331)'];
        echo $yarClient->call("send", array(0 => array($params)));
    }

    /**
     *  航行服务 hexin-zg
     */
    public function testhexinzgAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/quote/hexinzg?@appid=58eae00591d05&&auth-token=");
        $params = ['Method' => 'Quote', 'Fuquan' => 'Q', 'CodeList' => '33(300635)',
            'DataType' => '11;367250', 'DateTime' => '16384(20170331-20170331)'];
        echo $yarClient->call("send", array(0 => array($params)));
    }

    /**
     *  航行服务 hexin-zg_hq
     */
    public function testhexinzghqAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/quote/quotezg?@appid=58eae00591d05&&auth-token=");
        $params = ['Method' => 'Quote', 'Fuquan' => 'Q', 'CodeList' => '33(300635)',
            'DataType' => '11;367250', 'DateTime' => '16384(20170331-20170331)'];
        echo $yarClient->call("send", array(0 => array($params)));
    }

    /**
     * RPC内部缓存管理--iwencaiOpenApiToken
     */
    public function testappcacheiwencaiopenapitokenAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/cache/appcachemanager?@appid=58eae00591d05&&auth-token=");
        $params = ['reload' => '1', 'appid' => '4549152Eb776', 'secret' => 'efa9e9209a6efd7e0b47d5992baa4421'];
        echo $yarClient->call("iwencaiOpenapiToken", array(0 => array($params)));
    }

    /**
     * RPC内部缓存管理--rpcToken
     */
    public function testappcacherpctokenAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/cache/appcachemanager?@appid=58eae00591d05&&auth-token=");
        $params = ['reload' => '1', 'appid' => '4549152Eb776', 'secret' => 'efa9e9209a6efd7e0b47d5992baa4421'];
        echo $yarClient->call("rpcToken", array(0 => array($params)));
    }

    /**
     * RPC内部缓存管理--getCache
     */
    public function testappcachegetcacheAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/cache/appcachemanager?@appid=58eae00591d05&&auth-token=");
        $params = ['reload' => '1', 'appid' => '4549152Eb776', 'secret' => 'efa9e9209a6efd7e0b47d5992baa4421'];
        echo $yarClient->call("getCache", array(0 => array($params)));
    }

    /**
     * RPC内部缓存管理--delCache
     */
    public function testappcachedelcacheAction()
    {
        $yarClient =
            new Yar_Client("http://rpc.myhexin.com/api/cache/appcachemanager?@appid=58eae00591d05&&auth-token=");
        $params = ['reload' => '1', 'appid' => '4549152Eb776', 'secret' => 'efa9e9209a6efd7e0b47d5992baa4421'];
        echo $yarClient->call("delCache", array(0 => array($params)));
    }

}