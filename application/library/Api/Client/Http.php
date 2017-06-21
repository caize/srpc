<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/27
 * Time: 9:44
 * Desc: CURL http客户端程序
 */
namespace Api\Client;

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
    /**
     * Curl handler
     * @access private
     * @var resource
     */
    protected $_ch;
    protected $_userAgent = "Mozilla/5.0 (X11; CentOs; Linux x86_64) Gecko/20100101 Firefox/28.0";
    protected $_reqHeader = array();

    protected $_timeout = 3;

    protected $_getParamsArr = array();
    protected $_postParamsArr = array();

    public function __construct($url = null)
    {
        $this->init();
        if (null !== $url) {
            $this->setUrl($url);
        }
    }

    /**
     * Init Curl session
     * @access public
     */
    public function init()
    {
        $this->_ch = curl_init();
        curl_setopt($this->_ch, CURLOPT_FAILONERROR, true);

        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, true);

        // use gzip if possible
        curl_setopt($this->_ch, CURLOPT_ENCODING, 'gzip, deflate');

        curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, 0);

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
        curl_setopt($this->_ch, CURLOPT_USERPWD, "$username:$password");
        return $this;
    }

    /**
     * Set referrer
     * @param string $referrer_url
     * @access public
     */
    public function setReferrer($referrerUrl)
    {
        curl_setopt($this->_ch, CURLOPT_REFERER, $referrerUrl);
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
        curl_setopt($this->_ch, CURLOPT_USERAGENT, $useragent);
        return $this;
    }

    /**
     * Set proxy to use for each curl request
     * @param string $proxy
     * @access public
     */
    public function setProxy($proxy)
    {
        curl_setopt($this->_ch, CURLOPT_PROXY, $proxy);
        return $this;
    }

    /**
     * 设置SSL模式
     */
    public function setSSLVerify($verify = true)
    {
        curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, $verify);
        curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, $verify);
        return $this;
    }

    public function setMethod($method)
    {
        if (strtoupper($method) == self::HTTP_POST) {
            curl_setopt($this->_ch, CURLOPT_POST, true);
            curl_setopt($this->_ch, CURLOPT_HTTPGET, false);
            $this->_httpMethod = self::HTTP_POST;
        } else {
            curl_setopt($this->_ch, CURLOPT_POST, false);
            curl_setopt($this->_ch, CURLOPT_HTTPGET, true);
            $this->_httpMethod = self::HTTP_GET;
        }
        return $this;
    }

    public function setHeaderOut($enable = true)
    {
        curl_setopt($this->_ch, CURLINFO_HEADER_OUT, $enable);
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
        return $this;
    }

    public function setInterface($ip)
    {
        curl_setopt($this->_ch, CURLOPT_INTERFACE, $ip);
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

    public function request()
    {
        $url = $this->_baseUrl;
        if ($this->_httpMethod == self::HTTP_POST && !empty($this->_postParamsArr)) {
            $queryArr = '';
            foreach ($this->_postParamsArr as $key => $item) {
                $queryArr[] = "{$key}={$item}";
            }
            curl_setopt($this->_ch, CURLOPT_POSTFIELDS, implode('&', $queryArr));
        } elseif (!empty($this->_getParamsArr)) {
            $url .= '?' . http_build_query($this->_getParamsArr);
        }
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_ch, CURLOPT_TIMEOUT, $this->_timeout);
        return $this->execute();
    }

    /**
     * Fetch data from target URL
     * and store it directly to file
     * @param string $url
     * @param resource $fp stream resource(ie. fopen)
     * @param string $ip address to bind (default null)
     * @param int $timeout in sec for complete curl operation (default 5)
     * @return boolean true on success false othervise
     * @access public
     */
    public function download($fp)
    {
        //set method to get
        curl_setopt($this->_ch, CURLOPT_HTTPGET, true);
        // store data into file rather than displaying it
        curl_setopt($this->_ch, CURLOPT_FILE, $fp);
        return $this->execute();
    }


    /**
     * Set file location where cookie data will be stored and send on each new request
     * @param string $cookieFile path to cookie file (must be in writable dir)
     * @access public
     */
    public function storeCookies($cookieFile)
    {
        // use cookies on each request (cookies stored in $cookie_file)
        curl_setopt($this->_ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($this->_ch, CURLOPT_COOKIEFILE, $cookieFile);
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
        if (is_array($cookie)) {
            $tCookieArr = [];
            foreach ($cookie as $k => $v) {
                $tCookieArr[] = "{$k}={$v}";
            }
            $cookie = implode('; ', $tCookieArr);
        }
        curl_setopt($this->_ch, CURLOPT_COOKIE, $cookie);
    }

    /**
     * Get last URL info
     * usefull when original url was redirected to other location
     * @access public
     * @return string url
     */
    public function getEffectiveUrl()
    {
        return curl_getinfo($this->_ch, CURLINFO_EFFECTIVE_URL);
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

    /**
     * Close curl session and free resource
     * Usually no need to call this function directly
     * in case you do you have to call init() to recreate curl
     * @access public
     */
    public function close()
    {
        curl_close($this->_ch);
    }

    /**
     * 获取CURL资源句柄
     */
    public function getHandle()
    {
        return $this->_ch;
    }

    protected function execute()
    {
        if (count($this->_reqHeader) > 0) {
            $headers = array();
            foreach ($this->_reqHeader as $k => $v) {
                $headers[] = "$k: $v";
            }
            curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $headers);
        }
        $result = curl_exec($this->_ch);
        $this->httpCode = curl_getinfo($this->_ch, CURLINFO_HTTP_CODE);
        $this->runtime = curl_getinfo($this->_ch, CURLINFO_TOTAL_TIME);
        //获取编码
        $contentType = curl_getInfo($this->_ch, CURLINFO_CONTENT_TYPE);
        if (preg_match('/charset=(.*)/', curl_getInfo($this->_ch, CURLINFO_CONTENT_TYPE), $arr)) {
            $this->responseCharset = $arr[1];
        }
        if (curl_errno($this->_ch)) {
            $this->errCode = curl_errno($this->_ch);
            $this->errMsg = curl_error($this->_ch) . '[' . $this->errCode . ']';
			$this->close();
            return false;
        } else {
			$this->close();
            return $result;
        }
    }
}
