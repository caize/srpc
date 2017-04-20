<?php
namespace Api\Third\Iwencai\Api;

use Api\Client\Http;

class Token
{
    protected $_appid;
    protected $_secret;
    protected $_host;

    public function __construct($appid, $secret)
    {
        $this->_appid = $appid;
        $this->_secret = $secret;
    }

    public function setHost($host)
    {
        $this->_host = $host;
        return $this;
    }

    public function exec()
    {
        $http = new Http();
        if ($this->_host === null) {
            $url = \Yaf\Registry::get('config')->url->iwencai->openapi->host;
        } else {
            $url = $this->_host;
        }
        $http->setUrl($url . '/auth/v1/token');
        $http->setParamsGet(
            array(
                'appid' => $this->_appid,
                'secret' => $this->_secret
            )
        );
        $result = $http->request();
        $json = json_decode($result, true);
        return array(
            'errorcode' => $json['status_code'],
            'errormsg' => $json['status_msg'],
            'access_token' => $json['access_token'],
            'expire_in' => $json['expire_in']
        );
    }
}