<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/18
 * Time: 14:26
 */
namespace Syar\Protocol;
class Result
{
    protected $_header = array();
    protected $_body = null;
    public function setHeader($k, $val)
    {
        $this->_header[$k] = $val;
        return $this;
    }
    public function setBody($body)
    {
        $this->_body = $body;
        return $this;
    }

    public function getData()
    {
        return array(
            'header' => $this->_header,
            'body' => $this->_body
        );
    }

    public function getHeader()
    {
        return $this->_header;
    }

    public function getBody()
    {
        return $this->_body;
    }
}