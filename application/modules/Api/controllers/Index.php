<?php
/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
use \Externalapi\QuotationModel;
use \Api\Controller\Rpc;
use \Hprose\Client;

class IndexController extends Rpc
{

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/api/index/index/index/name/root 的时候, 你就会发现不同
     */
    public function indexAction()
    {

        var_dump(Yaf\Registry::get('routerMapData'));
        exit();
        var_dump($_SERVER);
        echo 'test redis cache';
        $config = new \Yaf\Config\Ini(APPLICATION_PATH_CONFIG . '/redis.ini', APPLICATION_ENV);
        $redisConfig = $config->toArray();
        $cacheObj = \Api\Cachemanager::getCache($redisConfig);
        var_dump($cacheObj->get('test'));
        if ($cacheObj->get('test') !== false) {
            return;
        }
        $cacheObj->set('test', time());
        $cacheObj->expire('test', 10);

        return;
        echo 1222;

        return false;
        $testModel = new QuotationModel();
        $this->_rpcService->start($testModel);
        //$yarServer = new Yar_Server($testModel);
        //$yarServer->handle();
        return false;
    }

    public function testAction()
    {
        //$filelog = new Filelog(array());
        var_dump($_SERVER);
        var_dump($this->_request->getCookie('user'));
        var_dump($_COOKIE);
        echo 'test';
        return false;
    }

    public function hproseAction()
    {
        \Yaf\Loader::import('Hprose/Autoload/Hprose.php');
        $client = Client::create(
            'http://lg.api.10jqka.com.cn/test/index/index?Method=Quote'
            . '&CodeList=17(603388)&DataType=11;3672520&DateTime=16384%2820170324-1%29&Fuquan=Q'
            . '&DupCode=0&_lang=other', false
        );
        print_r($client->Quote());
        //print_r($client->hexin());
        return false;
    }

    public function yarclientAction()
    {
        $client = new Yar_Client(
            'http://lg.api.10jqka.com.cn/test/index/index/?method=quote&codelist=33(300033)&'
            . 'datetime=0(0-0)&fuquan=Q&formula=period:16384;ID:7615;NAME:默认名字'
            . ';source:emw6PUVNQSggKENMT1NFLU1BKENMT1NFLDcpKS9NQShDTE9TRSw3KSo0ODAsMikqNTsgDQpza'
            . 'Do9RU1BKCAoQ0xPU0UtTUEoQ0xPU0UsMTEpKS9NQShDTE9TRSwxMSkqNDgwLDcpKjU7IA0KQTE6PXpsP'
            . 'j0xODAgQU5EIFJFRih6bCwxKTwxODA7DQpTRUxFQ1QgQTEgQU5EIEJBUlNMQVNUKFJFRihBMSwxKSk+QkFSU0'
            . 'xBU1Qoemw+MCBBTkQgUkVGKHpsLDEpPD0wKTs=;'
        );
        $reponse = $client->Quote();
        //$reponse = $client->hexin();
        print_r($reponse);
        exit();
        return false;
    }
}
