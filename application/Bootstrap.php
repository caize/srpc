<?php
/**
 * @name Bootstrap
 * @author root
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf\Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 *
 *
 *
 * 如果使用swoole,最好把所有需要的插件全部提前注册，然后在业务中进行验证。
 * 这样可以减少每次分发时的的一系列初始化操作，提升性能
 * 注意：用dispatch(yaf_http_request)分发，这里只会在在swoole启动的时候进来，
 *      用bootsrap()->run() , 每次都会执行，性能只能提升4-5倍
 */
use Yaf\Bootstrap_Abstract;
use Yaf\Dispatcher;
use Yaf\Registry;
use Yaf\Route\Map;
use Api\Router\Rewrite;
class Bootstrap extends Yaf\Bootstrap_Abstract
{
//    public function _initLoadLibrary()
//    {
//        set_include_path(get_include_path() . PATH_SEPARATOR . '/usr/local/lib/php');
//    }

    public function _initConfig()
    {
        //把配置保存起来
        $objConfig = Yaf\Application::app()->getConfig();
        Yaf\Registry::set('config', $objConfig->toArray());

        $config = new \Yaf\Config\Ini(APPLICATION_PATH_CONFIG . '/redis.ini', APPLICATION_ENV);
        $redisConfig = $config->toArray();
        Yaf\Registry::set('redisConfig', $redisConfig);
        Registry::set('projectBaseUrl', $objConfig->application->baseUrl);
    }

    /**
     * 注册本地第三方库
     */
    public function _initLoadNameSpace(Yaf\Dispatcher $dispatcher)
    {
        Yaf\Loader::getInstance()->registerLocalNamespace(array('Api', 'Hprose', 'Syar'));
    }

    public function _initRoute(Yaf\Dispatcher $dispatcher)
    {
        //$request = $dispatcher->getRequest();
        //在这里注册自己的路由协议,默认使用简单路由
        $router = $dispatcher->getRouter();
        $router->addRoute('routeRewrite', new Rewrite());
        //var_dump($router);
        //var_dump('=================');
        //\Yaf\Dispatcher::getInstance()->getRequest()->isPost()
    }


    public function _initPlugin(Yaf\Dispatcher $dispatcher)
    {
        $request = $dispatcher->getRequest();
        //设置默认路由
        $requestUri = $request->getRequestUri();
        //var_dump($requestUri);
        $requestUriArr = explode('/', ltrim($requestUri, '/'));
        $defaultModule = '';
        if (count($requestUriArr) >= 3) {
            $defaultModule = strtolower($requestUriArr[0]);
        }

        //注册一个插件
        $pluginsObj = new Yaf\Config\Ini(APPLICATION_PATH_CONFIG . '/systemplugins.ini', APPLICATION_ENV);
        $plugins = $pluginsObj->toArray();
        $registerPluginsArr = array();
        if (isset($plugins['systems'])) {
            $registerPluginsArr = $plugins['systems'];
        }
        if (isset($plugins[$defaultModule]) && is_array($plugins[$defaultModule])) {
            $registerPluginsArr = array_merge($registerPluginsArr, $plugins[$defaultModule]);
        }

//        /**
//         * 优化swoole下的处理,插件全部注册
//         */
//        foreach ($plugins as $key => $items) {
//            $registerPluginsArr = array_merge($registerPluginsArr, $items);
//        }
        foreach ($registerPluginsArr as $plugin) {
            $obj = new $plugin();
            $dispatcher->registerPlugin($obj);
        }
    }

    public function _initView(Yaf\Dispatcher $dispatcher)
    {
        $dispatcher->autoRender(false);
        //echo '_initView', "<br>";
        //在这里注册自己的view控制器，例如smarty,firekylin
    }


    public function _initLaravelDb(Yaf\Dispatcher $dispatcher)
    {
        \Yaf\Loader::import(\Yaf\Loader::getInstance()->getLibraryPath() . '/vendor/autoload.php');
        $ini = new \Yaf\Config\Ini(APPLICATION_PATH_CONFIG . '/db.ini', APPLICATION_ENV);
        $config = $ini->serviceapi->toArray();
        $capsule = new \Illuminate\Database\Capsule\Manager();
        $capsule->addConnection($config);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    public function _initCache(Yaf\Dispatcher $dispatcher)
    {
        $redisConfig = Yaf\Registry::get('redisConfig');
        $cacheObj = \Api\Cachemanager::getCache($redisConfig);
        Yaf\Registry::set('cacheManager', $cacheObj);
    }
}
