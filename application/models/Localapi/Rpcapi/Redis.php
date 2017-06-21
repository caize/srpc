<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/10
 * Time: 13:34
 */
namespace Localapi\Rpcapi;

use Api\Globals\Defined;
use Rpc\RpclocalModel;
use Yaf\Registry;

class RedisModel extends RpclocalModel
{
    const CACHE_SERVICE_PREFIX = 'cacheservice_';
    const LEFT = 1;
    const RIGHT = 2;
    const CACHE_SERVICE_KEYLIST = 'cacheservicelist';
    const SAVE_TYPE_SET = 1;
    const SAVE_TYPE_DEL = 2;

    protected function _getKey($appName, $key)
    {
        if (is_string($key))
            return self::CACHE_SERVICE_PREFIX . strtolower($appName . '_' . $key);
        else {
            foreach ($key as $k => &$v) {
                $v = self::CACHE_SERVICE_PREFIX . strtolower($appName . '_' . $v);
            }
            return $key;
        }
    }

    /**
     * @return \Api\Cache\Redis
     */
    private function _getCacheService()
    {
        return Registry::get('cacheManager');
    }

    protected function _checkParams($params)
    {
        if (!isset($params['appName']) || empty($params['appName'])) {
            throw new \Exception(
                Defined::MSG_API_CACHE_FAILED_NOT_FOUND_APPNAME,
                Defined::CODE_API_CACHE_FAILED_NOT_FOUND_APPNAME
            );
        }

        if (key_exists('key', $params) && empty($params['key'])) {
            throw new \Exception(
                Defined::MSG_API_CACHE_FAILED_NOT_FOUND_KEY,
                Defined::CODE_API_CACHE_FAILED_NOT_FOUND_KEY
            );
        }

        if (key_exists('expire', $params) && $params['expire'] <= 0) {
            throw new \Exception(
                Defined::MSG_API_CACHE_FAILED_NOT_FOUND_EXPIRE,
                Defined::CODE_API_CACHE_FAILED_NOT_FOUND_EXPIRE
            );
        }
        if (key_exists('val', $params)
            && (
                is_array($params['val']) && empty($params['val'])
                || !is_array($params['val']) && !isset($params['val']{0})
            )
        ) {
            throw new \Exception(
                Defined::MSG_API_CACHE_FAILED_NOT_FOUND_VAL,
                Defined::CODE_API_CACHE_FAILED_NOT_FOUND_VAL
            );
        }
        return true;
    }

    /**
     * Get the value related to the specified key
     *
     * @param   string $appName '项目名称' #英文小写
     *          string  key => '自定义缓存key' 内部存储会自动凭借 $appname_$key
     * @return  json
     * @example $redis->get('free', 'test');
     */
    public function get($appName, $key)
    {
        return $this->_send(
            function ($appName, $key) {
                $checkParams['appName'] = $appName;
                $checkParams['key'] = $key;
                $this->_checkParams($checkParams);
                return $this->_getCacheService()->get($this->_getKey($appName, $key));
            }, array($appName, $key)
        );
    }

    /**
     * @param $appName
     * @param $array
     * @return \Rpc\json|\Rpc\xml
     */
    public function mGet($appName, $array)
    {
        if (is_string($array)) {
            $array = [$array];
        }
        return $this->_send(
            function ($appName, $array) {
                $checkParams['appName'] = $appName;
                $this->_checkParams($checkParams);
                $data = $this->_getCacheService()->mGet($this->_getKey($appName, $array));
                $returnData = [];
                foreach ($data as $key => $val) {
                    $returnData[preg_replace('/' . self::CACHE_SERVICE_PREFIX . '/', '', $key, 1)] = $val;
                }
                return $returnData;
            }, array($appName, array_filter($array))
        );
    }

    /**
     * @param $appName 项目名称
     * @param string $key 缓存key
     * @param string $val 设置的值 字符串
     * @param $expire 有效期
     */
    public function set($appName, $key, $val, $expire)
    {
        return $this->_send(
            function ($appName, $key, $val, $expire) {
                $checkParams['appName'] = $appName;
                $checkParams['key'] = $key;
                $checkParams['expire'] = $expire;
                $checkParams['val'] = $val;
                $this->_checkParams($checkParams);
                $this->_setCacheData($this->_getKey($appName, $key), $val, $expire);
                $this->_saveCacheKeyList($appName, $key, $expire);
                return [];
            }, array($appName, $key, $val, $expire)
        );
    }

    /**
     * @param string $appName
     * @param array $keyvals
     * @param int $expire
     * @return \Rpc\json|\Rpc\xml
     */
    public function mSet($appName, $keyVals, $expire)
    {
        return $this->_send(
            function ($appName, $keyVals, $expire) {
                $checkParams['appName'] = $appName;
                $checkParams['expire'] = $expire;
                $this->_checkParams($checkParams);
                foreach ($keyVals as $k => $val) {
                    $this->_setCacheData($this->_getKey($appName, $k), $val, $expire);
                }
                $this->_saveCacheKeyList($appName, array_keys($keyVals), $expire);
                return [];
            }, array($appName, $keyVals, $expire)
        );
    }

    /**
     * @param $appName
     * @param $key string|array
     */
    public function del($appName, $key)
    {
        return $this->_send(
            function ($appName, $key) {
                $checkParams['appName'] = $appName;
                $this->_checkParams($checkParams);
                $keys = [];
                if (is_string($key)) {
                    $keys[] = $key;
                } else {
                    $keys = $key;
                }
                foreach ($keys as $k) {
                    $this->_getCacheService()->del($this->_getKey($appName, $k));
                }
                $this->_saveCacheKeyList($appName, $keys, 0, self::SAVE_TYPE_DEL);
                return [];
            }, array($appName, $key)
        );
    }

    /**
     * @param $appname
     * @param $key
     * @param string|array $vals 插入队列数据
     */
    public function push($appName, $key, $vals)
    {
        return $this->_send(
            function ($appName, $key, $vals) {
                $checkParams['appName'] = $appName;
                $checkParams['key'] = $key;
                $checkParams['val'] = $vals;
                $this->_checkParams($checkParams);
                if (is_string($vals)) {
                    $vals = [$vals];
                }
                foreach ($vals as $v) {
                    $this->_getCacheService()->push($this->_getKey($appName, $key), $v);
                }
                return [];
            }, array($appName, $key, $vals)
        );
    }

    /**
     * @param $appname
     * @param $key
     * @param $num 获取个数
     */
    public function pop($appName, $key, $num = 1)
    {
        return $this->_send(
            function ($appName, $key, $num) {
                $checkParams['appName'] = $appName;
                $checkParams['key'] = $key;
                $this->_checkParams($checkParams);
                $data = [];
                while ($num-- > 0) {
                    $v = $this->_getCacheService()->pop($this->_getKey($appName, $key));
                    if (!$v) {
                        break;
                    }
                    $data[] = $v;
                }
                return $data;
            }, array($appName, $key, $num)
        );
    }

    /**
     * @param $appName
     * @param $key
     * @param string $hasKey hastable 字段
     * @return \Rpc\json|\Rpc\xml
     */
    public function hGet($appName, $key, $hasKey)
    {
        return $this->_send(
            array($this, '_doHGet'), array($appName, $key, $hasKey)
        );
    }

    /**
     * @param $appName
     * @param $key
     * @param array $hasKeys hastable 字段
     * @return \Rpc\json|\Rpc\xml
     */
    public function hMGet($appName, $key, $hasKeys)
    {
        return $this->_send(
            array($this, '_doHGet'), array($appName, $key, $hasKeys)
        );
    }

    /**
     * @param string $appName
     * @param string $key
     * @param string $hasKey
     * @param string $hasVal
     * @param int $expire
     * @return \Rpc\json|\Rpc\xml
     */
    public function hSet($appName, $key, $hasKey, $hasVal, $expire = 0)
    {
        return $this->hMSet($appName, $key, array($hasKey => $hasVal), $expire);
    }

    /**
     * @param string $appName
     * @param string $key
     * @param array $hasKeyVals
     * @param int $expire
     * @return \Rpc\json|\Rpc\xml
     */
    public function hMSet($appName, $key, $hasKeyVals, $expire = 0)
    {
        return $this->_send(
            function ($appName, $key, $hasKeyVals, $expire) {
                $checkParams['appName'] = $appName;
                $checkParams['key'] = $key;
                $this->_checkParams($checkParams);
                $this->_getCacheService()->hmSet($this->_getKey($appName, $key), $hasKeyVals);
                if ($expire > 0) {
                    $this->_getCacheService()->expire($this->_getKey($appName, $key), $expire);
                }
                $this->_saveCacheKeyList($appName, $key, $expire);
                return [];
            }, array($appName, $key, $hasKeyVals, $expire)
        );
    }

    /**
     * @param $appName
     * @param $key
     * @param string|array $fileds
     * @return \Rpc\json|\Rpc\xml
     */
    public function hDel($appName, $key, $fileds)
    {
        return $this->_send(
            function ($appName, $key, $fileds) {
                $checkParams['appName'] = $appName;
                $checkParams['key'] = $key;
                $this->_checkParams($checkParams);
                if (is_string($fileds)) {
                    $this->_getCacheService()->hDel($this->_getKey($appName, $key), $fileds);
                } else {
                    foreach ($fileds as $field) {
                        $this->_getCacheService()->hDel($this->_getKey($appName, $key), $field);
                    }
                }
                $this->_saveCacheKeyList($appName, $key, 0, self::SAVE_TYPE_DEL);
                return [];
            }, array($appName, $key, $fileds)
        );
    }

    public function hGetAll($appName, $key)
    {
        return $this->_send(
            function ($appName, $key) {
                $checkParams['appName'] = $appName;
                $checkParams['key'] = $key;
                $this->_checkParams($checkParams);
                return $this->_getCacheService()->hGetAll($this->_getKey($appName, $key));
            }, array($appName, $key)
        );
    }

    public function batchDelCache($appName, $keyPre, $run = 'try')
    {
        return $this->_send(
            function ($appName, $keyPre, $run) {
                $checkParams['appName'] = $appName;
                $checkParams['key'] = $keyPre;
                $this->_checkParams($checkParams);
                if (strlen($keyPre) <= 4) {
                    throw new \Exception(
                        Defined::MSG_API_CACHE_FAILED_KEY_LENGTH . '! > 4',
                        Defined::CODE_API_CACHE_FAILED_KEY_LENGTH
                    );
                }
                $data = \Illuminate\Database\Capsule\Manager::table('redis_cachekey')
                    ->select('cachekey')
                    ->orWhere(
                        function ($query) {
                            $query->where('expire', '=', '0000-00-00 00:00:00')
                                ->orWhere('expire', '>', date('Y-m-d H:i:s'));
                        }
                    )
                    ->where('cachekey', 'like', $appName . '_' . $keyPre . '%')
                    ->where('isvalid', '=', 1)
                    ->get();
                $returnData = array();
                foreach ($data as $obj) {
                    $returnData[] = $obj->cachekey;
                }
                if ($run == 'execute') {
                    foreach ($returnData as $k) {
                        $this->_getCacheService()->del(self::CACHE_SERVICE_PREFIX . $k);
                    }
                    \Illuminate\Database\Capsule\Manager::table('redis_cachekey')
                        ->select('cachekey')
                        ->orWhere(
                            function ($query) {
                                $query->where('expire', '=', '0000-00-00 00:00:00')
                                    ->orWhere('expire', '>', date('Y-m-d H:i:s'));
                            }
                        )
                        ->where('cachekey', 'like', $appName . '_' . $keyPre . '%')
                        ->where('isvalid', '=', 1)
                        ->update(array('isvalid' => 1, 'mtime' => date('Y-m-d H:i:s')));

                }
                return json_decode($data);
            }, array($appName, $keyPre, $run)
        );
    }

    protected function _doHGet($appName, $key, $hasKey)
    {
        $checkParams['appName'] = $appName;
        $checkParams['key'] = $key;
        $this->_checkParams($checkParams);
        if (is_string($hasKey)) {
            return $this->_getCacheService()->hGet($this->_getKey($appName, $key), $hasKey);
        } else {
            return $this->_getCacheService()->hmGet($this->_getKey($appName, $key), $hasKey);
        }
    }

    /**
     * @param $appName
     * @param $keys
     * @param $expire
     * @param int $type 1set 2 del
     * @return bool
     */
    protected function _saveCacheKeyList($appName, $keys, $expire, $type = self::SAVE_TYPE_SET)
    {
        $keys = is_string($keys) ? [$keys] : $keys;
        $data['createTime'] = time();
        $data['expire'] = $expire > 0 ? $data['createTime'] + $expire : 0;
        $data['app'] = $appName;
        $data['keys'] = $keys;
        $data['type'] = $type;
        $this->_getCacheService()->push(self::CACHE_SERVICE_KEYLIST, $data);
        return true;
    }
}