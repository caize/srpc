<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/31
 * Time: 17:37
 */
namespace Api\Cache;
use Api\Iface\Cache;
abstract  class Cabstract implements Cache
{
    protected $_config;
    protected $_cachePrefix = 'hq_b2cweb_';
    protected $_store;
    protected $_projectKey = '';


    public function __construct($config)
    {
        $this->_config = $config;
        if (isset($config['prefix'])) {
            $this->_cachePrefix = $config['prefix'];
        }
        if (isset($config['projectPrefix'])) {
            $this->_projectKey = $config['projectPrefix'];
        }
    }

    public function setProjectKey($key)
    {
        $this->_projectKey = $key . '_';
        return $this;
    }

    public function getStore()
    {
        if ($this->_store === null) {
            $this->_connect();
        }
        if (!$this->_heartbeat()) {
            $this->_connect(1);
        }
        return $this->_store;
    }

    public function mSet($keyVals)
    {
        $flag = true;
        foreach ($keyVals as $key => $val) {
            $flag = $flag & $this->set($key, $val);
        }
        return $flag;
    }
    /**
     * @return string
     */
    public function getCacheKey($key)
    {
        return $this->_cachePrefix . $this->_projectKey . $key;
    }
    abstract protected function _heartbeat();
    abstract protected function _connect($reconn = 0);
}