<?php

/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Yaf\Controller_Abstract
{

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/api/index/index/index/name/root 的时候, 你就会发现不同
     */
    public function indexAction($name = "Stranger")
    {
        echo 'hello world!';
        return;
//var_dump($_SERVER);
        echo '<pre>';
        var_dump($this->_request);
        var_dump($this->_request->getParams());
        echo 1111111;
        return false;
        var_dump($this->getRequest()->getServer('HTTP_ACCESS_TOKEN'));
        //1. fetch query
        $get = $this->getRequest()->getQuery("get", "default value");
        //2. fetch model
        $model = new SampleModel();

        //3. assign
        $this->getView()->assign("content", $model->selectSample());
        $this->getView()->assign("name", $name);

        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return TRUE;
    }

    function __get($name)
    {
        // TODO: Implement __get() method.
    }
}
