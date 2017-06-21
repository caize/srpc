<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/6
 * Time: 13:32
 */
namespace Common;
class ResultModel
{
    /**
     * 默认失败代码
     * @var int
     */
    const DEFAULT_ERROR_CODE = -1000;

    /**
     * 默认成功代码
     * @var int
     */
    const SUCCESS_CODE = 0;

    /**
     * 返回结果
     * @var mixed
     */
    protected $_result;

    /**
     * 返回结果信息
     * @var string
     */
    protected $_resultMsg;

    /**
     * 正确代码
     * 当结果代码与正确代码相同时，表示执行结果有效（正确）
     * @var int
     */
    protected $_successCode	= self::SUCCESS_CODE;

    /**
     * 结果代码（默认是执行成功）
     * @var int
     */
    protected $_resultCode	= self::SUCCESS_CODE;

    /**
     * 错误代码对应的错误信息
     * @var Array
     */
    protected $_msgs		= array();

    /**
     * 要返回的数据
     * @var Array
     */
    protected $_resultData	= null;

    /**
     * 初始化结果类
     * @param Array $result
     * 初始化数组必须为set方法支持的，键名与set方法去掉set首字母小写一致
     */
    public function __construct(array $result = array())
    {
        foreach ($result as $k => $v) {
            $setMethod = 'set' . ucfirst($k);
            if (method_exists($this, $setMethod)) {
                $this->$setMethod($v);
            }
        }
    }

    /**
     * 当结果代码与正确代码相同时，表示执行结果有效（正确）
     * @return Bool
     */
    public function isValid()
    {
        return $this->_resultCode === $this->_successCode;
    }

    /**
     * 设置执行结果代码
     * @param Integer|string $code
     */
    public function setResultCode($code)
    {
        $this->_resultCode = $code;
        return $this;
    }

    /**
     * 获取执行结果代码
     * @return Integer
     */
    public function getResultCode()
    {
        return $this->_resultCode;
    }

    /**
     * 设置执行结果代码
     * @param type $data string/array
     */
    public function setResultData($data = null)
    {
        $this->_resultData = $data;
        return $this;
    }

    /**
     * 获取执行结果代码
     * @return Integer
     */
    public function getResultData()
    {
        return $this->_resultData;
    }

    /**
     * 设置执行结果提示信息
     * @param String $msg
     */
    public function setResultMsg($msg)
    {
        $this->_resultMsg = (string) $msg;
        return $this;
    }

    /**
     * 获取执行结果信息
     * 如果未设置过结果信息，将会用$resultCode匹配$msgs里面的信息
     * @return String
     */
    public function getResultMsg()
    {
        if (empty($this->_resultMsg)) {
            return isset($this->_msgs[$this->_resultCode]) ? $this->_msgs[$this->_resultCode] : '';
        }
        return $this->_resultMsg;
    }

    /**
     * 设置结果
     * @param Mixed $result
     */
    public function setResult($result)
    {
        $this->_result = $result;
        return $this;
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
            'result'	=>$this->getResultData()
        );
    }
}