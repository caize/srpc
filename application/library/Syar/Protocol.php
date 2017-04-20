<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/13
 * Time: 14:43
 */
namespace Syar;

use Swoole\Http\Request;
use Yaf\Response\Http;
use Syar\Protocol\Result;
class Protocol
{
    protected $_pack = null;
    /**
     * @var Yar;
     */
    protected $_yar = null;
    protected $_callBackClass = null;

    public function __construct($classObj)
    {
        $this->_callBackClass = $classObj;
    }

    public function getPack()
    {
        if ($this->_pack === null) {
            $this->_pack = new Pack();
        }
        return $this->_pack;
    }

    public function onRequest($rowContent, $metmod)
    {
        if (strtolower($metmod) != 'post') {
            return $this->_response(
                array('errorcode' => -1001, 'errormsg' => 'http metmod error')
            );
        }
        $this->_yar = $this->getPack()->unpack($rowContent);

        if ($this->_yar->isError()) {
            // 解包错误
            return $this->_response(
                array('errorcode' => -1001, 'errormsg' => $this->_yar->isError())
            );
        }
        $method = $this->_yar->getRequestMethod();
        $params = $this->_yar->getRequestParams();
        $rs = $this->process($method, $params);
        return $this->_response($rs);
    }

    /**
     * @param string $data
     */
    protected function _response($data)
    {
        $resultObj = new Result();
        if (null !== $this->_yar && !$this->_yar->isError()) {
            if ($data['errorcode'] == 200) {
                $this->_yar->setReturnValue($data['rs']);
            } else {
                $this->_yar->setError($data['errormsg']);
            }
            $resultObj->setHeader('Content-Type', 'application/octet-stream');
            $result = $this->getPack()->pack($this->_yar);
        } else {
            if (null !== $this->_yar) {
                $result = $this->_yar->getError();
            } elseif (isset($data['errormsg'])) {
                $result = json_encode($data);
            } else {
                $result = null;
            }
        }
        // 输出返回
        $resultObj->setBody($result);
        return $resultObj;
    }

    /**
     * @param $method
     * @param $params
     * @return array
     */
    public function process($method, $params)
    {
        try {
            if (!method_exists($this->_callBackClass, $method)) {
                throw new \Exception('not found method in class');
            }
            return [
                'errorcode' => 200,
                'rs' => call_user_func_array(array($this->_callBackClass, $method), $params)
            ];
        } catch (\Exception $e) {
            $error = [
                'errorcode' => $e->getCode(),
                'errormsg' => $e->getMessage(),
                'debug' => $e->getTraceAsString()
            ];
            return $error;
        }
    }
}