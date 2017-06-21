<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/3/31
 * Time: 20:00
 */
use \Externalapi\QuotationModel;
use \Api\Controller\Base;
use \Hprose\Client;
use \Illuminate\Database\Capsule\Manager as DB;

class DebugController extends Base
{
    public function indexAction()
    {
        if ($this->_request->isPost()) {
            $url = $this->baseUrl . $this->_request->getParam('url');
            $urlParamsArr = $this->_request->getParam('urlParamskey', array());
            $isDebug = 0;
            if (in_array('isdebug', $urlParamsArr)) {
                $isDebug = 1;
            }
            $queryStr = array();

            $cacheManager = \Yaf\Registry::get('cacheManager');
            $redisConfig = \Yaf\Registry::get('redisConfig');
            //获取源token
            $appid = '58eae00591d05';
            $secret = '079b18da78622fddc4a98d4f37fff3f2b5060289';
            $token = $cacheManager->get($redisConfig['api']['tokenappid'] . $appid);
            if (!$token) {
                $tokenModel = new \Auth\TokenModel();
                $result = $tokenModel->getToken($appid, $secret);
                $data = $result->getResultData();
                $token = $data['token'];
            }

            $urlParamsValue = $this->_request->getParam('urlParamsValue', array());
            foreach ($this->_request->getParam('urlParamskey', array()) as $k => $item) {
                $queryStr[] = "{$item}={$urlParamsValue[$k]}";
            }
//            $params = array_combine(
//                $this->_request->getParam('urlParamskey', array()),
//                $this->_request->getParam('urlParamsValue', array())
//            );
            $funcName = $this->_request->getParam('funcName');
            $funcParamsKey = $this->_request->getParam('funcParamskey', array());
            $funcParamsValue = $this->_request->getParam('funcParamsValue', array());
            $funcCallParams = array();
            foreach ($funcParamsKey as $k => $item) {
                if (empty($funcParamsValue[$k])) {
                    unset($funcParamsValue[$k]);
                    unset($funcParamsKey[$k]);
                    continue;
                }
                $item = trim($item);
                if ($item == '.')
                    $item = '';
                if (false === strpos($item, '.')) {
                    if (empty($item)) {
                        $funcCallParams[] = $funcParamsValue[$k];
                    } else {
                        if (empty($funcCallParams)) {
                            $funcCallParams[][$item] = $funcParamsValue[$k];
                        } else {
                            end($funcCallParams);
                            $endKey = key($funcCallParams);
                            if (is_array($funcCallParams[$endKey])) {
                                $funcCallParams[$endKey][$item] = $funcParamsValue[$k];
                            }
                        }
                    }
                } else {
                    $pos = strpos($item, '.');
                    $key = substr($item, 0, $pos);
                    if (empty($key)) {
                        $key = '';
                    }
                    $key2 = substr($item, $pos + 1);
                    if (empty($key)) {
                        $funcCallParams[][$key2] = $funcParamsValue[$k];
                    } else {
                        $key = 'debug_' . $key;
                        $funcCallParams[$key][$key2] = $funcParamsValue[$k];
                    }
                }
            }
            $url = $url . '?@appid=' . $appid . '&' . implode('&', $queryStr);
            $varExport = var_export(array_values($funcCallParams), true);
            $varExport = preg_replace('/,\s*\)/', ')', $varExport);
            $demoStr = '$yarClient = new Yar_Client("' . $url . '&auth-token=");<br/>';
            $demoStr .= '$data = $yarClient->call("' . $funcName . '", ' . $varExport . ');';
            if ($isDebug) {
                require_once APPLICATION_PATH . '/Sdk/Swoole/Yar/Http.php';
                $yarClient = new \Swoole\Yar\Http($url . '&auth-token=' . $token);
            } else {
                $yarClient = new Yar_Client($url . '&auth-token=' . $token);
                $yarClient->setOpt(YAR_OPT_TIMEOUT, 50000);
            }
            try {
                $data = $yarClient->call($funcName, array_values($funcCallParams));
                $data = json_decode($data, true);

            } catch (Yar_Client_Exception $e) {
                $data['errorcode'] = $e->getCode();
                $data['errormsg'] = $e->getMessage();
            }
            $view = $this->getView();
            $view->result = $data;
            $view->demoStr = $demoStr;
            $this->display('show');
            return;
        }
        $serviceModel = new \Admin\RouterModel();
        $rows = $serviceModel->getList();
        $wikiArr = [];
        foreach ($rows as $key => $items) {
            if ($items['router'] == 'api/token/get') {
                unset($rows[$key]);
                continue;
            }
            if (empty($items['parameter'])) {
                $rows[$key]['parameter'] = array();
            } else {
                $rows[$key]['parameter'] = json_decode($items['parameter'], true);
            }
            if (!empty($items['wiki'])) {
                $wikiArr[$items['id']] = $items['wiki'];
            }
        }
        $view = $this->getView();
        $view->list = $rows;
        $view->wiki = json_encode($wikiArr);
//        $view->token = $token;
        $this->display();
    }

    public function queryapiAction()
    {
        $url = $this->baseUrl . $this->_request->getParam('url');
        $urlParamsArr = (array)$this->_request->getParam('urlParamskey');
        $isDebug = 0;
        if (in_array('isdebug', $urlParamsArr)) {
            $isDebug = 1;
        }
        $queryStr = array();
        $cacheManager = \Yaf\Registry::get('cacheManager');
        $redisConfig = \Yaf\Registry::get('redisConfig');
        //获取源token
        $appid = '58eae00591d05';
        $secret = '079b18da78622fddc4a98d4f37fff3f2b5060289';
        $token = $cacheManager->get($redisConfig['api']['tokenappid'] . $appid);
        if (!$token) {
            $tokenModel = new \Auth\TokenModel();
            $result = $tokenModel->getToken($appid, $secret);
            $data = $result->getResultData();
            $token = $data['token'];
        }
        $urlParamsValue = $this->_request->getParam('urlParamsValue', array());
        $urlParamsValue = json_decode($urlParamsValue, true);
        $urlParamsKey = $this->_request->getParam('urlParamskey', array());
        $urlParamsKey = json_decode($urlParamsKey, true);
        foreach ($urlParamsKey as $k => $item) {
            $queryStr[] = "{$item}={$urlParamsValue[$k]}";
        }
        $funcName = $this->_request->getParam('funcName');
        $funcParamsKey = $this->_request->getParam('funcParamskey', array());
        $funcParamsKey = json_decode($funcParamsKey, true);
        $funcParamsValue = $this->_request->getParam('funcParamsValue', array());
        $funcParamsValue = json_decode($funcParamsValue, true);
        $funcCallParams = array();
        foreach ($funcParamsKey as $k => $item) {
            if (empty($funcParamsValue[$k])) {
                unset($funcParamsValue[$k]);
                unset($funcParamsKey[$k]);
                continue;
            }
            $item = trim($item);
            if ($item == '.')
                $item = '';
            if (false === strpos($item, '.')) {
                if (empty($item)) {
                    $funcCallParams[] = $funcParamsValue[$k];
                } else {
                    if (empty($funcCallParams)) {
                        $funcCallParams[][$item] = $funcParamsValue[$k];
                    } else {
                        end($funcCallParams);
                        $endKey = key($funcCallParams);
                        if (is_array($funcCallParams[$endKey])) {
                            $funcCallParams[$endKey][$item] = $funcParamsValue[$k];
                        }
                    }
                }
            } else {
                $pos = strpos($item, '.');
                $key = substr($item, 0, $pos);
                if (empty($key)) {
                    $key = '';
                }
                $key2 = substr($item, $pos + 1);
                if (empty($key)) {
                    $funcCallParams[][$key2] = $funcParamsValue[$k];
                } else {
                    $key = 'debug_' . $key;
                    $funcCallParams[$key][$key2] = $funcParamsValue[$k];
                }
            }
        }
        $url = $url . '?@appid=' . $appid . '&' . implode('&', $queryStr);
        $varExport = var_export(array_values($funcCallParams), true);
        $varExport = preg_replace('/,\s*\)/', ')', $varExport);
        $demoStr = '$yarClient = new Yar_Client("' . $url . '&auth-token=");<br/>';
        $demoStr .= '$data = $yarClient->call("' . $funcName . '", ' . $varExport . ');';
        if ($isDebug) {
            $yarClient = new \Syar\Tclient($url . '&auth-token=' . $token);
        } else {
            $yarClient = new Yar_Client($url . '&auth-token=' . $token);
            $yarClient->setOpt(YAR_OPT_TIMEOUT, 50000);
        }
        try {
            $data = $yarClient->call($funcName, array_values($funcCallParams));
            $data = json_decode($data, true);

        } catch (Yar_Client_Exception $e) {
            $data['errorcode'] = $e->getCode();
            $data['errormsg'] = $e->getMessage();
        }
        $res['result'] = $data;
        $res['procedure'] = $demoStr;
        echo json_encode($res);
    }

    public function getfuncAction()
    {
        $uri = $this->_request->getParam('uri', '');
        $http = new \Api\Client\Http();
        $parseUrl = parse_url($this->baseUrl);
        $http->setUrl($parseUrl['scheme'] . '://127.0.0.1' . $uri);
        $http->setHeader('Host', $parseUrl['host']);
        $response = $http->request();
        preg_match_all('/<h2 onclick="_t\(this\)"><u>\+<\/u>[^:]*::([^\(]*).*?<\/h2>/', $response, $arr);
        $funcArr = array();
        if (isset($arr[1])) {
            $funcArr = $arr[1];
        }
        $data = array(
            'errorcode' => 0,
            'errormsg' => '',
            'func' => $funcArr,
            'html' => $response
        );
        $this->_changeToJson();
        $view = $this->getView();
        $view->func = $funcArr;
        $view->html - $response;
        $this->display();
    }
}
