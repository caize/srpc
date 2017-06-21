<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/6/19
 * Time: 10:58
 */

namespace Api\Client\Coroutine;
use Api\Iface\Request;

class Http implements Request
{
    public $info;

    const HTTP_POST = 'POST';
    const HTTP_GET = 'GET';

    /**
     * Contain last error message if error occured
     * @access private
     * @var string
     */
    public $errMsg = '';

    public $errCode = 0;
    public $httpCode;
    public $runtime = 0;
    public $responseCharset = null;
    protected $_httpMethod;
    protected $_baseUrl;
    protected $_cookieArr = [];
    /**
     * @var \Swoole\Http\Client
     */
    protected $_httpClient;
    /**
     * @access private
     * @var resource
     */
    protected $_userAgent = "Mozilla/5.0 (X11; CentOs; Linux x86_64) Gecko/20100101 Firefox/28.0";
    protected $_reqHeader = array();

    protected $_timeout = 3;

    protected $_getParamsArr = array();
    protected $_postParamsArr = array();

    public function __construct($url = null)
    {
        if (null !== $url) {
            $this->setUrl($url);
        }
        $this->_httpMethod = self::HTTP_GET;
    }

    public function setUrl($url)
    {
        $this->_baseUrl = $url;
        return $this;
    }

    /**
     * Set username/pass for basic http auth
     * @param string $username
     * @param string $password
     * @access public
     */
    public function setCredentials($username, $password)
    {
        //@TODO
        return $this;
    }

    /**
     * Set referrer
     * @param string $referrer_url
     * @access public
     */
    public function setReferrer($referrerUrl)
    {
        $this->_reqHeader['Referer'] = $referrerUrl;
        return $this;
    }

    /**
     * Set client's useragent
     * @param string $_userAgent
     * @access public
     */
    public function setUserAgent($useragent = null)
    {
        $this->_userAgent = $useragent;
        $this->_reqHeader['User-Agent'] = $useragent;
        return $this;
    }

    /**
     * Set proxy to use for each curl request
     * @param string $proxy
     * @access public
     */
    public function setProxy($proxy)
    {
        //@TODO
        return $this;
    }

    /**
     * 设置SSL模式
     */
    public function setSSLVerify($verify = true)
    {
        //@TODO
        return $this;
    }

    public function setMethod($method)
    {
        if (strtoupper($method) == self::HTTP_POST) {
            $this->_httpMethod = self::HTTP_POST;
        } else {
            $this->_httpMethod = self::HTTP_GET;
        }
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
        return $this;
    }

    public function setParamsPost(array $postData)
    {
        $this->_postParamsArr = $postData;
        return $this;
    }

    public function setParamPost($key, $val)
    {
        $this->_postParamsArr[$key] = $val;
        return $this;
    }

    public function setParamsGet(array $getData)
    {
        $this->_getParamsArr = $getData;
        return $this;
    }

    public function setParamGet($key, $val)
    {
        $this->_getParamsArr[$key] = $val;
        return $this;
    }

    public function setHeader($k, $v)
    {
        $this->_reqHeader[$k] = $v;
    }

    public function addHeaders(array $header)
    {
        $this->_reqHeader = array_merge($this->_reqHeader, $header);
    }

    /**
     * Set custom cookie
     * @param string|array $cookie
     * @access public
     */
    public function setCookie($cookie)
    {
        if (!is_array($cookie)) {
            $cookieArr = explode(';', $cookie);
            $cookie = [];
            foreach ($cookieArr as $item) {
                $arr = explode('=', $item);
                $key = isset($arr[0]) ? trim($arr[0]) : '';
                $val = isset($arr[1]) ? trim($arr[1]) : '';
                $cookie[$key] = $val;
            }
        }
        $this->_cookieArr = $cookie;
        return $this;
    }

    /**
     * Get http response code
     * @access public
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function close()
    {
        if ($this->_httpClient !== null) {
            $this->_httpClient->close();
        }
    }

    /**
     * 获取httpClient
     */
    public function getHandle()
    {
        return $this->_httpClient;
    }



    public function request()
    {
        $parseUrl = parse_url($this->_baseUrl);
        $ip = gethostbyname($parseUrl['host']);
        $port = isset($parseUrl['port']) ? $parseUrl['port'] : '80';

        if (!isset($this->_reqHeader['Host'])) {
            $this->_reqHeader['Host'] = $parseUrl['host'];
        }
        $this->_httpClient = new \Swoole\Coroutine\Http\Client($ip, $port);
        $this->_httpClient->set(['timeout' => $this->_timeout]);
        $this->_httpClient->setHeaders($this->_reqHeader);
        //$this->_httpClient->setDefer(); //延迟收包，配置 recv()使用
        $requestPath = isset($parseUrl['path']) ? $parseUrl['path'] : '';
        $requestPath .= isset($parseUrl['query']) ? '?' . $parseUrl['query'] : '';
        $startTime = microtime(true);
        //var_dump($this->_httpClient->getDefer());
        if ($this->_httpMethod == self::HTTP_GET) {
            if (false === strpos($requestPath, '?')) {
                $requestPath .= '?';
            }
            $this->_httpClient->get($requestPath . http_build_query($this->_getParamsArr));
        } else {
            $this->_httpClient->post($requestPath, $this->_postParamsArr);
        }
        $this->runtime = microtime(true) - $startTime;
        $result = false;
        if ($this->_httpClient->errCode == 110 || !isset($this->_httpClient->statusCode)) {
            $this->errCode = $this->_httpClient->errCode;
            $this->errMsg = "Connection No Response; Operation timed out after " . ($this->_timeout * 1000)
                . ' milliseconds [' . $this->_httpClient->errCode . ']';
            $this->httpCode = 0;
        } else {
            if ($this->_httpClient->statusCode >= 200 && $this->_httpClient->statusCode <= 299) {
                $result = $this->_httpClient->body;
            } else {
                $this->errCode = $this->_httpClient->statusCode;
                $this->errMsg = \Api\Globals\Defined::getHttpCodeMessage($this->_httpClient->statusCode);
            }
            $this->httpCode = $this->_httpClient->statusCode;
        }
        return $result;
    }

    public function __destruct()
    {
        //echo '$this->close();';
        $this->close();
    }
}

