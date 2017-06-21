<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/6/5
 * Time: 14:58
 */
namespace Swoole\Yar;
class Parseurl
{
    protected $_scheme;
    protected $_host;
    protected $_port = '80';
    protected $_path;
    protected $_query;
    protected $_fragment ;

    public function __construct($url = null)
    {
        if (null !== $url) {
            $parseUrl = parse_url($url);
            if (is_array($parseUrl)) {
                foreach ($parseUrl as $k => $val) {
                    $this->{"_$k"} = $val;
                }
            }
        }
    }

    public function setScheme($scheme)
    {
        $this->_scheme = $scheme;
        return $this;
    }

    public function setHost($host)
    {
        $this->_host = $host;
        return $this;
    }

    public function setPort($port)
    {
        $this->_port = $port;
        return $this;
    }

    public function setPath($path)
    {
        $this->_path = $path;
        return $this;
    }
    public function setQuery($query)
    {
        $this->_query = $query;
        return $this;
    }

    public function setFragment($fragment)
    {
        $this->_fragment = $fragment;
        return $this;
    }

    public function getScheme()
    {
        return $this->_scheme;
    }

    public function getHost()
    {
        return $this->_host;
    }

    public function getPort()
    {
        return $this->_port;
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function getQuery()
    {
        return $this->_query;
    }

    public function getFragment()
    {
        return $this->_fragment;
    }


}