<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/6
 * Time: 14:12
 */
namespace Api\View;
use Yaf\View_Interface;
class Json implements View_Interface
{
    /**
     * 返回数据包
     *
     * @var array
     */
    private $_retData = null;

    /**
     * 回调函数
     */
    private $_callBack = null;

    /**
     * 初始化返回数据格式
     */
    public function __construct()
    {
        $this->_retData = array(
            'errorcode' => 0,
            'errormsg' => '',
            'result' => new \stdClass()
        );
    }

    public function __isset($key)
    {
        return isset($this->_retData[$key]);
    }

    public function __get($key)
    {
        return isset($this->{$key}) ? $this->_retData[$key] : null;
    }

    public function __set($key, $val)
    {
        $this->assign($key, $val);
    }

    public function assign($key, $val = null)
    {
        $this->_retData[$key] = $val;
        return $this;
    }


    public function setCallback($cb)
    {
        $this->_callBack = $cb;
        return $this;
    }

    /**
     * @see Light_View_Abstract
     */
    public function render($name = null, $value = null)
    {
        $jsonData = json_encode($this->_retData);
        if ($this->_callBack) {
            $jsonData = $this->_callBack . '(' . $jsonData . ')';
        }
        return $jsonData;
    }


    public function display($name = null, $value = null)
    {
        echo $this->render();
    }
    public function setScriptPath($dir)
    {

    }
    public function getScriptPath()
    {

    }
}