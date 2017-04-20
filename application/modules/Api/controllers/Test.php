<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/31
 * Time: 20:00
 */
use \Externalapi\QuotationModel;
use \Api\Controller\Rpc;
use \Hprose\Client;
use \Illuminate\Database\Capsule\Manager as DB;

class TestController extends Rpc
{
    public function testAction()
    {
        $testModel = new \Externalapi\RpchttpModel($this->_request);
        $this->_rpcService->start($testModel);
    }

    public function cacheAction()
    {
        echo 'test redis cache';
        $config = new \Yaf\Config\Ini(APPLICATION_PATH_CONFIG . '/redis.ini', APPLICATION_ENV);
        $redisConfig = $config->toArray();
        $cacheObj = \Api\Cachemanager::getCache($redisConfig);
        var_dump($cacheObj->get('test'));
        exit();
    }

    public function quoteAction()
    {
        $testModel = new \Externalapi\RpchttpModel($this->_request);
        $this->_rpcService->start($testModel);
    }

    public function hproseAction()
    {
        //\Yaf\Loader::import('Hprose/Autoload/Hprose.php');
        $client = Client::create(
            'http://lg.api.10jqka.com.cn/api/test/dbtestrpc?&_lang=other', false
        );
        print_r($client->dbtest());
        //print_r($client->hexin());
        return false;
    }

    public function yarclientAction()
    {
        $client = new Yar_Client(
            'http://lg.api.10jqka.com.cn/test/test/index/?'
            . 'method=quote&codelist=33(300033)&datetime=0(0-0)&'
            . 'fuquan=Q&formula=period:16384;ID:7615;NAME:默认名字;'
            . 'source:emw6PUVNQSggKENMT1NFLU1BKENMT1NFLDcpKS9NQShDTE9TRSw3KSo0ODAsMikqNTsgD'
            . 'QpzaDo9RU1BKCAoQ0xPU0UtTUEoQ0xPU0UsMTEpKS9NQShDTE9TRSwxMSkqNDgwLDcpKjU7IA0KQTE6PXps'
            . 'Pj0xODAgQU5EIFJFRih6bCwxKTwxODA7DQpTRUxFQ1QgQTEgQU5EIEJBUlNMQVNUKFJFRihBMSwxKSk+QkFSU0xBU1'
            . 'Qoemw+MCBBTkQgUkVGKHpsLDEpPD0wKTs=;'
        );
        $reponse = $client->Quote();
        //$reponse = $client->hexin();
        print_r($reponse);
        exit();
        return false;
    }

    public function indexAction()
    {
        echo 1;
        return false;
    }
    public function dbtestrpcAction()
    {
        $table = new \Externalapi\Business\Test1Model();
        $this->_rpcService->start($table);
    }

    public function dbtestAction()
    {
        $table = new \Externalapi\Business\Test1Model();
        var_dump($table->dbtest());
        //$this->_response->setHeader('Content-Type','text/html; charset=UTF-8');
        //var_dump(\Illuminate\Database\Capsule\Manager::table('api')->get());
        //return;
        return;
        var_dump($table->get()->toArray());
        exit();
        return false;
        try {
            var_dump($test);
            exit();
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }
}