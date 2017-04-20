<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/5
 * Time: 19:35
 */
namespace Api\Globals;
class Defined
{
    const CODE_API_AUTH_FAILED_NOT_MAP = -10000;
    const MSG_API_AUTH_FAILED_NOT_MAP = 'auth faild';
    const CODE_API_AUTH_FAILED_NOT_FOUND_TOKEN = -10001;
    const MSG_API_AUTH_FAILED_NOT_FOUND_TOKEN = 'auth faild, token check faild!';
    const CODE_API_AUTH_FAILED_NOT_FOUND_APPID = -10002;
    const MSG_API_AUTH_FAILED_NOT_FOUND_APPID = 'auth faild, appid check faild!';
    const CODE_API_AUTH_FAILED_NOT_FOUND_RESOURCE = -10003;
    const MSG_API_AUTH_FAILED_NOT_FOUND_RESOURCE = 'auth faild! no assign!';
    const CODE_API_AUTH_FAILED_NOT_FOUND_MAP_URL = -10004;
    const MSG_API_AUTH_FAILED_NOT_FOUND_MAP_URL = 'not found router map url,please call system manager!';
    const CODE_API_AUTH_FAILED_ADD_TOKEN = -10005;
    const MSG_API_AUTH_FAILED_ADD_TOKEN = 'check token auth failed!';
    const CODE_API_HTTP_FAILED_CURL = -10006;
    const CODE_API_AUTH_FAILED_THIRD_NOT_FOUND = -10007;
    const MSG_API_AUTH_FAILED_THIRD_NOT_FOUND = 'the third auth check secret not round';
    const CODE_API_AUTH_FAILED_ADD_TOKEN_IWENCAI = -10008;


    const OTHER_AUTH_DEFAULT = 'default';
    const OTHER_AUTH_IWENCAI = 'iwencai';

    const SYSTEM_PROTOCOL_SWOOLE = 'swoole';
    public static function getOtherAuthArray()
    {
        return array(self::OTHER_AUTH_IWENCAI);
    }
}