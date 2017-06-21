<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/6
 * Time: 13:17
 */
namespace Auth;
use \Illuminate\Database\Capsule\Manager as DB;
use Common\ResultModel;
use Api\Globals\Defined;
use Yaf\Registry;
class TokenModel extends \BaseModel
{
    /**
     * @param $appid
     * @param $secret
     * @return ResultModel
     */
    public function getToken($appid, $secret)
    {
        $resultObj = new ResultModel();
        if (empty($appid) || empty($secret)) {
            $resultObj->setResultCode(Defined::CODE_API_AUTH_FAILED_ADD_TOKEN);
            $resultObj->setResultMsg(Defined::MSG_API_AUTH_FAILED_ADD_TOKEN);
            return $resultObj;
        }
        $row = DB::table('app')->select('appid')
            ->where("appid", '=', $appid)
            ->where("secret", "=", $secret)
            ->where("isvalid", "=", "1")->first();
        if (empty($row)) {
            $resultObj->setResultCode(Defined::CODE_API_AUTH_FAILED_ADD_TOKEN);
            $resultObj->setResultMsg(Defined::MSG_API_AUTH_FAILED_ADD_TOKEN);
            return $resultObj;
        }
        $authToken = sha1($appid . $secret . microtime(true));
        $resultObj->setResultData(array('token' => $authToken));
        //she cache
        $cacheManager = Registry::get('cacheManager');
        $redisConfig = Registry::get('redisConfig');
        //获取源token
        $oldToken = $cacheManager->get($redisConfig['api']['tokenappid'] . $appid);
        if ($oldToken) {
            $cacheManager->del($redisConfig['api']['token'] . $oldToken);
        }
        //生成新的token
        $expire = 86400;
        try {
            $cacheManager->set($redisConfig['api']['token'] . $authToken, $appid);
            $cacheManager->set($redisConfig['api']['tokenappid'] . $appid, $authToken);
            $cacheManager->expire($redisConfig['api']['token'] . $authToken, $expire);
            $cacheManager->expire($redisConfig['api']['tokenappid'] . $appid, $expire);
        } catch (\Exception $e) {
            Registry::get('log')->put(
                ' url add token' . $e->getMessage(), \Api\Log::ERROR
            );
            $resultObj->setResultCode(Defined::CODE_API_AUTH_FAILED_ADD_TOKEN);
            $resultObj->setResultMsg(Defined::MSG_API_AUTH_FAILED_ADD_TOKEN);
            return $resultObj;
        }
        $resultObj->setResultData(array('token' => $authToken, 'expire' => $expire));
        return $resultObj;
    }
}