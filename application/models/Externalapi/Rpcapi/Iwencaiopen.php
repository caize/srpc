<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/12
 * Time: 13:48
 */
namespace Externalapi\Rpcapi;
use Api\Client\Http;
use Api\Globals\Defined;
use Api\Globals\Functions;
use Rpc\RpchttpModel;
class IwencaiopenModel extends RpchttpModel
{
    /**
     * @var array 只支持get接口配置
     */
    protected $_getMap = array(
        'search/v1/stockyuqing' => 1,
        'search/v1/dataextract' => 1,
        'chatbot/v1/controller/query' => 1,
        'cbas/v1/recommend' => 1,
    );
    protected function authThirdType()
    {
        return Defined::OTHER_AUTH_IWENCAI;
    }

    /**
     * @param array $params
     *     对应http请求的参数
     *     例问财接口访问：http://openapi.iwencai.com.cn/kg/v1/concept_all?test=1
     *     有两种方式进行交互
     *     1.http://api.10jqka.com.cn/api/iwencai/openapi?@reqtype=kg/v1/concept_all&test=1&auth-token=;
     *          $params可以不穿
     *     2. 调用方法时传入，如 http://api.10jqka.com.cn/api/iwencai/openapi？auth-token=,
     *      $params = array(
     *          '@reqtype' => 'kg/v1/concept_all',
     *          'test' => 1,
     *      );
     *      注:如果同时传入http参数和call中的params参数，相同的key以params参数为准
     *      其中@reqtype代码需要转发的问财具体接口
     *      需要本地认证token绑定问财token
     * @desc
     *  注：认证token，在url中增加auth-token参数，此只针对需要认证的服务有效
     * @return json|xml
     */
    public function send($params = array())
    {
        if (!is_array($params)) {
            $params = json_decode($params, true);
        }
        //if (isset($params['@reqtype'])) {
            $params['@reqtype'] = ltrim($params['@reqtype'], '/');
        //    if (isset($this->_getMap[$params['@reqtype']])) {
                $this->_methodType = Http::HTTP_GET;
        //    }
        //}
        /**
         * 统一增加qid方便问财定位
         */
        $params['qid'] = md5(microtime(true));
        return parent::send($params);
    }

    /**
     * @param array $params
     * @param null $token
     * @desc 参照send，可以传@reqtype
     *  知识图谱-实体列表
     */
    public function graphConceptAll($params = array())
    {
        $params1['@reqtype'] = 'kg/v1/concept_all';
        return $this->send(Functions::apiParamsCheck($params, $params1));
    }
    /**
     * @param array $params
     *  array(
     *      'concept_name' => '央企国资改革',
     *      'begin_time' => '20100101',
     *      'end_time' => '30150101'
     * );
     * @param null $token
     * @desc 参照send，可以传@reqtype
     *  知识图谱-实体关系
     */
    public function graphConceptRelation($params)
    {
        $params1['@reqtype'] = 'kg/v1/concept_relation';
        return $this->send(Functions::apiParamsCheck($params, $params1));
    }
    /**
     * @param array $params
     *  array(
     * );
     * @param null $token
     * @desc 参照send，可以传@reqtype
     *  搜索技术-舆情
     */
    public function searchStockYuQing($params = array())
    {
        $params1['@reqtype'] = 'search/v1/stockyuqing';
        return $this->send(Functions::apiParamsCheck($params, $params1));
    }

    /**
     * @param array $params
     * @desc 拼写纠错
     */
    public function searchSpell($params = array())
    {
        $params1['@reqtype'] = 'search/v1/spell';
        return $this->send(Functions::apiParamsCheck($params, $params1));
    }

    /**
     * @param $params
     * @desc 机器人总控
     */
    public function chatbotController($params = array())
    {
        $params1['@reqtype'] = 'chatbot/v1/controller/query';
        return $this->send(Functions::apiParamsCheck($params, $params1));
    }

    /**
     * @param array $params
     * @desc cbas 个性化推荐接口
     * @return json
     */
    public function cbasRecommend($params = array())
    {
        $params1['@reqtype'] = 'cbas/v1/recommend';
        return $this->send(Functions::apiParamsCheck($params, $params1));
    }

    protected function _errorCodeKeyMap()
    {
        return 'status_code';
    }

    protected function _errorCodeSuccessVal()
    {
        return 0;
    }

    protected function _errorMsgKeyMap()
    {
        return 'status_msg';
    }

    protected function _resultDataMap()
    {
        return '__';
    }

    protected function _errorCallback($return, $params, $routerMap)
    {
        parent::_errorCallback($return, $params, $routerMap);
        /**
         * 如果提示鉴权失败重新生成token，下次访问使用新的token访问，
         * 防止缓存的token失效
         */
        if ($return['errorcode'] == -1) {
            $parseUrl = parse_url($routerMap['url']);
            $basePath = $parseUrl['host'];
            if (isset($parseUrl['port'])) {
                $basePath .= ":" . $parseUrl['port'];
            }
            if (isset($params['appid'])) {
                $tokenObj = new \Auth\TokenIwencaiModel();
                $tokenObj->getToken($params['appid'], $params['secret'], $basePath, true);
            } else {
                if (isset($routerMap['appid'])) {
                    $cache = \Rpc\RpcauthModel::getThirdAuthRedisCache(
                        $routerMap['appid'], $this->authThirdType()
                    );
                    if (!empty($cache)) {
                        $desc = json_decode($cache, true);
                        if (isset($desc['third_name']) && isset($desc['third_pwd'])) {
                            $tokenObj = new \Auth\TokenIwencaiModel();
                            $tokenObj->getToken(
                                $desc['third_name'], $desc['third_pwd'], $basePath, true
                            );
                        }
                    }
                }
            }

        }
    }
}

