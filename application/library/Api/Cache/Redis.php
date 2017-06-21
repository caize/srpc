<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/31
 * Time: 17:30
 */
namespace Api\Cache;
use Api\Cache\Cabstract;
class Redis extends Cabstract
{
    protected $_keyPing = 'test';
    public function __construct($config)
    {
        parent::__construct($config);
        if (isset($config['ping'])) {
            $this->_keyPing = $config['ping'];
        }
    }

    public function get($key)
    {
        return $this->getStore()->get($this->getCacheKey($key));
    }

    public function set($key, $val)
    {
        return $this->getStore()->set($this->getCacheKey($key), $val);
    }

    public function del($key)
    {
        return $this->getStore()->del($this->getCacheKey($key));
    }

    public function expire($key, $expire)
    {
        return $this->getStore()->expire($this->getCacheKey($key), $expire);
    }

    protected function _heartbeat()
    {
        try {
            return $this->_store->ping($this->_keyPing);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function _connect($reconn = 0)
    {
        if (null !== $this->_store && $reconn == 0) {
            return $this;
        }
        $connArr = $this->_config;
        if ($this->_store !== null) {
            $this->_store->close();
        }
        if ($connArr['enableCluster']) {
            $this->_store = new \RedisCluster(
                $connArr['clusterName'],
                $connArr['host'],
                $connArr['timeOut'],
                $connArr['timeOut']
            );
        } else {
            $host = '';
            $port = 0;
            if (is_array($connArr['host'])) {
                $tmp  = $connArr['host'][0];
            } else {
                $tmp  = $connArr['host'];
            }
            $tmp = explode(":", $tmp);
            $host = $tmp[0];
            $port = $tmp[1];
            $this->_store = new \Redis();
            $this->_store->connect($host, $port, $connArr['timeOut']);
            //集群暂时不支持加密
            if (!empty($connArr['auth'])) {
                $this->_store->auth($connArr['auth']);
            }
        }
        return $this;
    }

    public function lRange($key, $start, $end)
    {
        return $this->getStore()->lRange($key, $start, $end);
    }

    public function lPop($key)
    {
        return $this->getStore()->lPop($key);
    }

    public function hSet($key, $field, $val)
    {
        return $this->getStore()->hSet($this->getCacheKey($key), $field, $val);
    }

    public function hmSet($key, $fieldValsArr)
    {
        return $this->getStore()->hmSet($this->getCacheKey($key), $fieldValsArr);
    }

    public function hGet($key, $field)
    {
        return $this->getStore()->hGet($this->getCacheKey($key), $field);
    }

    public function hmGet($key, $fieldArr)
    {
        return $this->getStore()->hmGet($this->getCacheKey($key), $fieldArr);
    }

    public function hDel($key, $field)
    {
        return $this->getStore()->hDel($this->getCacheKey($key), $field);
    }

    public function hGetAll($key)
    {
        return $this->getStore()->hGetAll($this->getCacheKey($key));
    }

    public function push($key, $val)
    {
        if (is_array($val))
            $val = json_encode($val);
        return $this->getStore()->rpush($this->getCacheKey($key), $val);
    }

    public function pop($key)
    {
        return $this->getStore()->lpop($this->getCacheKey($key));
    }
    /**
     * @return Redis;
     */
    public function getStore()
    {
        return parent::getStore();
    }
}