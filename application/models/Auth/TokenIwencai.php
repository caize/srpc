<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/6
 * Time: 13:17
 */
namespace Auth;
use Common\ResultModel;
use Api\Globals\Defined;
use Yaf\Registry;
class TokenIwencaiModel extends \BaseModel
{
    /**
     * @param $appid
     * @param $secret
     * @return ResultModel
     */
    public function getToken($appid, $secret, $serverHost = null, $reload = false)
    {
        $resultObj = new ResultModel();
        if (empty($appid) || empty($secret)) {
            $resultObj->setResultCode(Defined::CODE_API_AUTH_FAILED_ADD_TOKEN_IWENCAI);
            $resultObj->setResultMsg(Defined::MSG_API_AUTH_FAILED_ADD_TOKEN);
            return $resultObj;
        }

        $cacheManager = Registry::get('cacheManager');
        //获取源token
        $cacheKey = 'tokeniwencai_' . $appid . $secret;
        $redisStatus = true;
        $token = false;
        if (!$reload) {
            try {
                $token = $cacheManager->get($cacheKey);
            } catch (\Exception $e) {
                //redis 异常改用文件存储
                $redisStatus = false;
                $token = $this->_getFileCacheToken($appid, $secret);
            }
        }
        if (!$token) {
            //生成新的token
            try {
                $iwencaiToken = new \Api\Third\Iwencai\Api\Token($appid, $secret);
                $result = $iwencaiToken->setHost($serverHost)->exec();
                if ($result['errorcode']) {
                    $resultObj->setResultCode(Defined::CODE_API_AUTH_FAILED_ADD_TOKEN_IWENCAI);
                    $resultObj->setResultMsg(Defined::MSG_API_AUTH_FAILED_ADD_TOKEN);
                    return $resultObj;
                }
                $token = $result['access_token'];
                if ($redisStatus) {
                    try {
                        $expire = floor($result['expire_in'] / 2);
                        if ($expire > 0) {
                            $cacheManager->set($cacheKey, $token);
                            $cacheManager->expire($cacheKey, $expire);
                        }
                    } catch (\Exception $e) {
                        $redisStatus = false;
                    }
                }

                if (!$redisStatus) {
                    $this->_setFileCacheToken($appid, $secret, $token, $result['expire_in']);
                }

            } catch (\Exception $e) {
                $resultObj->setResultCode(Defined::CODE_API_AUTH_FAILED_ADD_TOKEN_IWENCAI);
                $resultObj->setResultMsg(Defined::MSG_API_AUTH_FAILED_ADD_TOKEN);
                return $resultObj;
            }
        }
        $resultObj->setResultData(array('token' => $token));
        return $resultObj;
    }

    protected function _setFileCacheToken($appid, $secret, $token, $expire)
    {
        file_put_contents(
            $this->_getFileCacheName($appid, $secret),
            json_encode(array($token, time() + floor($expire / 2)))
        );
    }
    protected function _getFileCacheToken($appid, $secret)
    {
        $fileName  = $this->_getFileCacheName($appid, $secret);
        if (!file_exists($fileName)) {
            return false;
        }
        $data = json_decode(file_get_contents($fileName), true);
        if (!$data || $data[1] <= time()) {
            return false;
        }
        return $data[0];
    }

    protected function _getFileCacheName($appid, $secret)
    {
        $wencaiDir = APPLICATION_PATH_APP_REPOSITORY . '/iwencai';
        if (!is_dir($wencaiDir)) {
            mkdir($wencaiDir, '0755');
        }
        return $wencaiDir . '/' . sha1($appid . $secret);
    }
}