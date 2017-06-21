<?php

/**
 *
 * author:    l.gang06@yahoo.com
 * create:    2017-05-16
 *
 *  根据 Quote/Result/Base.php 改写
 */
namespace Quote\Result;
class Base
{

    /**
     * Gereral Failure
     */
    const SUCCESS = 0;

    /**
     * failure because of network problem
     */
    const FAILURE_NETWORK = -1;

    /**
     * failure because of not in auth
     */
    const FAILURE_NOT_AUTH = -2;

    /**
     * failure because of data format
     */
    const FAILURE_BAD_FORMAT = -3;

    /**
     * bad request
     */
    const FAILURE_BAD_REQUEST = -4;

    /**
     * success
     */
    const FAILURE = -5;

    /**
     * 错误号
     *
     * var number
     */
    protected $_error;

    /**
     * 错误信息
     *
     * var string
     */
    protected $_message;


    /**
     * 结果集
     *
     * var array
     */
    protected $_result = array();

    /*
    * 结果集编码
    *
    * var string
    */
    protected $_encode = 'utf-8';


    /**
     * 是否进行编码转换的标识
     *
     * var boolean
     */
    protected $_convert = false;


    /**
     * 构造函数
     *
     * @param String $encode 生成结果集的编码
     * @return void
     */
    public function __construct($encode = 'utf-8')
    {
        $this->_error = self::SUCCESS;
        if ($encode && $this->_encode != $encode) {
            $this->_convert = true;
            $this->_encode = $encode;
        }
    }

    /**
     * 根据设置转换编码
     *
     * @param String $input 输入的字符串
     * @return String 解码后的字符串
     */
    protected function _convertEncode($input)
    {
        if ($this->_convert) {
            return iconv('utf-8', $this->_encode, $input);
        } else {
            return $input;
        }
    }

    /**
     * 设置错误号
     */
    public function setError($err, $msg)
    {
        $this->_error = $err;
        $this->_message = $msg;
    }

    /**
     * 返回错误号
     *
     * @return number
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * 返回错误号
     *
     * @return number
     */
    public function getErrorMessage()
    {
        return $this->_message;
    }

    /**
     * 返回是否成功
     *
     * @return boolean
     */
    public function isSucceed()
    {
        return ($this->_error == self::SUCCESS) ? true : false;
    }

    /**
     * 解析结果集xml
     *
     * @param string $result
     * @return false|DOMDocument
     */
    protected function _parseResult($result)
    {
        //$result = iconv('gbk', 'utf-8', $result);
        if (!$this->_isValid($result)) {
            $this->_error = self::FAILURE_BAD_REQUEST;
            return false;
        }

        $doc = new \DOMDocument;
        if (!$doc->loadXML($result)) {
            $this->_error = self::FAILURE_BAD_FORMAT;
            return false;
        }
        $error = $doc->getElementsByTagName('Error');
        if ($error->length > 0) {
            switch ($error->item(0)->getAttribute('Id')) {
                case -1:
                    $err = self::FAILURE_BAD_REQUEST;
                    break;
                default:
                    $err = self::FAILURE_NOT_AUTH;
                    break;
            }
            $this->setError($err, $error->item(0)->textContent);
            return false;
        }
        return $doc;
    }

    /**
     * 检验请求结果的格式是否正确
     * @param type $xmlString
     */
    protected function _isValid($xmlString)
    {
        if (preg_match("/xml/", $xmlString)) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->_result;
    }
}