<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/10
 * Time: 16:42
 */
namespace Common\Result;
use Common\ResultModel;
class HttpModel extends ResultModel
{
    protected $_time = null;
    public function setResultTime($time)
    {
        $this->_time = $time;
        return $this;
    }

    public function getResultTime()
    {
        return $this->_time;
    }

    /**
     * 获取执行结果
     * @return Mixed
     */
    public function getResult()
    {
        return array(
            'errorcode'	=>$this->getResultCode(),
            'errormsg'	=>$this->getResultMsg(),
            'time' => $this->getResultTime(),
            'result'	=> $this->getResultData()
        );
    }
}