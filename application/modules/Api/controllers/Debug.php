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
            $params = array_combine(
                $this->_request->getParam('urlParamskey', array()),
                $this->_request->getParam('urlParamsValue', array())
            );
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
                        $funcCallParams[$key][$key2] = $funcParamsValue[$k];
                    }
                }
            }
            $url = $url . '?' . implode('&', $queryStr);
            $varExport = var_export(array_values($funcCallParams), true);
            $varExport = preg_replace('/,\s*\)/', ')', $varExport);
            $demoStr = '$yarClient = new Yar_Client("' . $url . '&auth-token=");<br/>';
            $demoStr .= '$data = $yarClient->call("' . $funcName . '", ' . $varExport . ');';
            $yarClient = new Yar_Client($url . '&auth-token=' . $token);
            //$yarClient->call('send', array($params, $this->_request->getParam('token')));
            try {
                $data = $yarClient->call($funcName, array_values($funcCallParams));
                //$data['result'] = mb_convert_encoding($data['result'], "UTF-8", "gbk,gb2312");
                $data = json_decode($data, true);
                if (isset($data['result']) && !empty($data['result'])) {
                    if (!empty(json_decode($data['result'], true))) {
                        $data['resultDecrypt'] = 'json_decode';
                        $data['result'] = json_decode($data['result'], true);
                    } elseif (@unserialize($data['result'])) {
                        $data['resultDecrypt'] = 'unserialize';
                        $data['result'] = unserialize($data['result']);
                    } else {
                        $data['resultDecrypt'] = 'null';
                    }
                }
            } catch (Yar_Client_Exception $e) {
                echo $e->getMessage();
            }
            $view = $this->getView();
            $view->result = $data;
            $view->demoStr = $demoStr;
            $this->display('show');
            return;
        }
        $serviceModel = new \Admin\RouterModel();
        $rows = $serviceModel->getList();
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
        }
        $view = $this->getView();
        $view->list = $rows;
        //$view->token = $token;
        $this->display();
    }

    public function getfuncAction()
    {
        $uri = $this->_request->getParam('uri', '');
        $http = new \Api\Client\Http();
        $http->setUrl($this->baseUrl . $uri);
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