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
    const CODE_API_AUTH_FAILED_IPTABLE_CHECK = -10009;
    const MSG_API_AUTH_FAILED_IPTABLE_CHECK = 'iptables check failed';
    const CODE_API_PARAM_CHECK_FAILED = -10010;
    const MSG_API_PARAM_CHECK_FAILED_IFINDSERVICEAUTH = 'params check error';
    const OTHER_AUTH_DEFAULT = 'default';
    const OTHER_AUTH_IWENCAI = 'iwencai';
    const OTHER_AUTH_LOCAL = 'local';
    const MSG_API_CACHE_FAILED_NOT_FOUND_APPNAME = 'not found cache appname';
    const CODE_API_CACHE_FAILED_NOT_FOUND_APPNAME = -10011;
    const MSG_API_CACHE_FAILED_NOT_FOUND_KEY = 'not found cache key';
    const CODE_API_CACHE_FAILED_NOT_FOUND_KEY = -10012;

    const MSG_API_CACHE_FAILED_NOT_FOUND_EXPIRE = 'not found cache expire';
    const CODE_API_CACHE_FAILED_NOT_FOUND_EXPIRE = -10013;


    const MSG_API_CACHE_FAILED_NOT_FOUND_VAL = 'cache value is empty';
    const CODE_API_CACHE_FAILED_NOT_FOUND_VAL = -10013;


    const MSG_API_CACHE_FAILED_KEY_LENGTH = 'cache key is to short!';
    const CODE_API_CACHE_FAILED_KEY_LENGTH = -10014;

    const CODE_API_AUTH_FAILED_SERVICE_OFFLINE = -10015;
    const MSG_API_AUTH_FAILED_SERVICE_OFFLINE = 'service offline!';

    const CODE_API_PARSE_FAILED_DATA = -10016;
    const MSG_API_PARSE_FAILED_DATA = 'result data parse failed!';

    const CODE_API_METHOD_DISABLED = -10017;
    const MSG_API_METHOD_DISABLED = 'this call method is disabled!';

    const CODE_TCP_PARSE_FAILED_DATA = -10018;
    const MSG_TCP_PARSE_FAILED_DATA = 'package parse failed!';

    const SYSTEM_PROTOCOL_SWOOLE = 'swoole';
    public static function getOtherAuthArray()
    {
        return array(self::OTHER_AUTH_IWENCAI, self::OTHER_AUTH_LOCAL);
    }

    public static function getHttpCodeMessage($code = null)
    {
        $httpCode["0"]="Unable to access";
        $httpCode["100"]="Continue";
        $httpCode["101"]="Switching Protocols";
        $httpCode["200"]="OK";
        $httpCode["201"]="Created";
        $httpCode["202"]="Accepted";
        $httpCode["203"]="Non-Authoritative Information";
        $httpCode["204"]="No Content";
        $httpCode["205"]="Reset Content";
        $httpCode["206"]="Partial Content";
        $httpCode["300"]="Multiple Choices";
        $httpCode["301"]="Moved Permanently";
        $httpCode["302"]="Found";
        $httpCode["303"]="See Other";
        $httpCode["304"]="Not Modified";
        $httpCode["305"]="Use Proxy";
        $httpCode["306"]="(Unused)";
        $httpCode["307"]="Temporary Redirect";
        $httpCode["400"]="Bad Request";
        $httpCode["401"]="Unauthorized";
        $httpCode["402"]="Payment Required";
        $httpCode["403"]="Forbidden";
        $httpCode["404"]="Not Found";
        $httpCode["405"]="Method Not Allowed";
        $httpCode["406"]="Not Acceptable";
        $httpCode["407"]="Proxy Authentication Required";
        $httpCode["408"]="Request Timeout";
        $httpCode["409"]="Conflict";
        $httpCode["410"]="Gone";
        $httpCode["411"]="Length Required";
        $httpCode["412"]="Precondition Failed";
        $httpCode["413"]="Request Entity Too Large";
        $httpCode["414"]="Request-URI Too Long";
        $httpCode["415"]="Unsupported Media Type";
        $httpCode["416"]="Requested Range Not Satisfiable";
        $httpCode["417"]="Expectation Failed";
        $httpCode["500"]="Internal Server Error";
        $httpCode["501"]="Not Implemented";
        $httpCode["502"]="Bad Gateway";
        $httpCode["503"]="Service Unavailable";
        $httpCode["504"]="Gateway Timeout";
        $httpCode["505"]="HTTP Version Not Supported";
        if ($code === null) {
            return $httpCode;
        } elseif (!isset($httpCode[$code])) {
            $rs = 'unknown http code';
        } else {
            $rs = $httpCode[$code];
        }
        return $rs . ' [' . $code . ']';
    }
}
