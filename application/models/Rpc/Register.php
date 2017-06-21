<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/6/14
 * Time: 11:26
 */

namespace Rpc;
use \Common\ResultModel;
use \Illuminate\Database\Capsule\Manager as DB;

class RegisterModel
{
    protected $_templatePath = null;
    protected $_controllerPath;
    protected $_modelPathHttp;
    protected $_modelPathLocal;
    public function __construct()
    {
        $this->_controllerPath = APPLICATION_PATH_APP . '/modules/Api/controllers';
        $this->_modelPathHttp = APPLICATION_PATH_APP . '/models/Externalapi/Rpcapi';
        $this->_modelPathLocal = APPLICATION_PATH_APP . '/models/Localapi/Rpcapi';

    }

    public function doHttp($params)
    {
        $resultModel = $this->_checkParams($params);
        if (!$resultModel->isValid()) {
            return $resultModel;
        }
        $paramsNew = $resultModel->getResultData();
        try {
            $resultTemplate = $this->_createTemplateContent($paramsNew);
            if (!$resultTemplate->isValid()) {
                return $resultTemplate;
            }
            DB::beginTransaction();
            $serviceModel = new \Admin\ServiceModel();
            $resultModel = $serviceModel->updateService($paramsNew);
            if (!$resultModel->isValid()) {
                throw new \Exception($resultModel->getResultMsg(), $resultModel->getResultCode());
            }
            $apidid = $resultModel->getResultData();
            //set routermap
            /**
             *  empty($params['module']) ||
             * empty($params['controller']) ||
             * empty($params['action']) ||
             * empty($params['apiid'])
             */
            $routerModel = new \Admin\RouterModel();
            $resultModel = $routerModel->updateService(
                [
                    'module' => $paramsNew['router']['module'],
                    'controller' => $paramsNew['router']['controller'],
                    'action' => $paramsNew['router']['action'],
                    'apiid' => $apidid
                ]
            );
            if (!$resultModel->isValid()) {
                throw new \Exception($resultModel->getResultMsg(), $resultModel->getResultCode());
            }
            //自动生成模板代码
            $templateData = $resultTemplate->getResultData();
            if (!file_exists($templateData['model']['filePath'])) {
                file_put_contents($templateData['model']['filePath'], $templateData['model']['content']);
            }
            file_put_contents($templateData['controller']['filePath'], $templateData['controller']['content']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $resultModel->setResultCode($e->getCode());
            $resultModel->setResultMsg($e->getMessage());
        }
        return $resultModel;
    }

    protected function _checkParams($params)
    {
        $resultModel = new ResultModel();
        if (APPLICATION_ENV != 'development') {
            $resultModel->setResultMsg('暂时只对开发环境开发');
            $resultModel->setResultCode(-2);
            return $resultModel;
        }
        if (!isset($params['serviceName']) || empty($params['serviceName'])) {
            $resultModel->setResultMsg('服务名称{serviceName}为空');
            $resultModel->setResultCode(-1);
            return $resultModel;
        }
        if (!isset($params['serviceUrl']) || empty($params['serviceUrl'])) {
            $resultModel->setResultMsg('服务地址{serviceUrl}为空');
            $resultModel->setResultCode(-1);
            return $resultModel;
        }
        if (!isset($params['router']) || empty($params['router'])) {
            $resultModel->setResultMsg('设置的路由地址{router}为空');
            $resultModel->setResultCode(-1);
            return $resultModel;
        }
        $params['router'] = strtolower($params['router']);
        $params['router'] = trim($params['router'], '/');
        $routerArr = explode('/', $params['router']);
        if ($routerArr[0] != 'api') {
            $resultModel->setResultMsg('路由model必须为api');
            $resultModel->setResultCode(-1);
            return $resultModel;
        } elseif (
            count($routerArr) != 3 || !isset($routerArr[1])
            || empty($routerArr[1]) || !isset($routerArr[2]) || empty($routerArr[2])
        ) {
            $resultModel->setResultMsg('路由规则不正确,示例:api/test/test');
            $resultModel->setResultCode(-1);
            return $resultModel;
        }
        if (!isset($params['returnKeyCode'])) {
            $resultModel->setResultMsg('设置接口返回状态码key,如果没有输入false');
            $resultModel->setResultCode(-1);
            return $resultModel;
        }

        $paramsNew = $params;
        $paramsNew['router'] = [
            'module' => $routerArr[0],
            'controller' => $routerArr[1],
            'action' => $routerArr[2],
            'model' => isset($params['modelName']) && !empty($params['modelName'])
                    ? $params['modelName'] : ucfirst($routerArr[2])
        ];
        $resultModel->setResultData($paramsNew);
        return $resultModel;
    }

    protected function _createTemplateContent($parmas)
    {
        $resultModel = $this->_createTemplateModelContent($parmas);
        if (!$resultModel->isValid() && ($parmas['ignore'] & 1) != 1) {
            return $resultModel;
        }
        $resultController = $this->_createTemplateContollerContent($parmas);
        if (!$resultController->isValid()) {
            return $resultController;
        }
        $resultController->setResultData(
            [
                'model' => $resultModel->getResultData(),
                'controller' => $resultController->getResultData()
            ]
        );
        return $resultController;
    }

    protected function _createTemplateContollerContent($params)
    {
        $resultModel = new ResultModel();
        $controllerPath = $this->_getTemplatePath() . '/Controller.php';
        $controllerTemplateContent = file_get_contents($controllerPath);
        $realyControllerPath = $this->_controllerPath . '/' . ucfirst($params['router']['controller']) . '.php';
        if (file_exists($realyControllerPath)) {
            if (!preg_match('/public\s+function.*?\s+}/is', $controllerTemplateContent, $arr)) {
                $resultModel->setResultCode(-4);
                $resultModel->setResultMsg('匹配action内容失败');
                return $resultModel;
            }
            $controllerTemplateContentNew = str_replace(
                [
                    '{ACTION}', '{MODEL_NAME}'
                ],
                [
                    $params['router']['action'],
                    $params['router']['model']
                ],
                $arr[0]
            );
            $fd = fopen($realyControllerPath, 'a+');
            $left = 0;
            $right = 0;
            $fileContentHeader = [];
            $fileContentFooter = [];
            while ($line = fgets($fd, 4096)) {
                if (preg_match("/public\s+function\s+{$params['router']['action']}Action/i", $line)) {
                    $resultModel->setResultCode(-5);
                    $resultModel->setResultMsg('action已经存在');
                    return $resultModel;
                }
                $lineNew = trim($line);
                if ($lineNew == '}') {
                    $fileContentHeader = array_merge($fileContentHeader, $fileContentFooter);
                    $fileContentFooter = [];
                }
                $fileContentFooter[] = $line;
            }
            fclose($fd);
            $realyControllerContextNew = implode('', $fileContentHeader) . "\n    "
                . $controllerTemplateContentNew . "\n" . implode('', $fileContentFooter);
            $resultModel->setResultData(
                [
                    'type' => 2,
                    'content' => $realyControllerContextNew,
                    'diff' => "\n    " . $controllerTemplateContentNew  . "\n",
                    'filePath' => $realyControllerPath
                ]
            );
        } else {
            $controllerTemplateContentNew = str_replace(
                [
                    '{DATE}', '{TIME}',
                    '{CONTROLLER}', '{ACTION}',
                    '{MODEL_NAME}'
                ],
                [
                    date('Y/M/d'), date('H:i'),
                    $params['router']['controller'], $params['router']['action'],
                    $params['router']['model']
                ],
                $controllerTemplateContent
            );
            $resultModel->setResultData(
                [
                    'type' => 1,
                    'content' => $controllerTemplateContentNew,
                    'filePath' => $realyControllerPath
                ]
            );
        }
        return $resultModel;
    }


    protected function _createTemplateModelContent($params)
    {
        $resultModel = new ResultModel();
        $modelFilePath = $this->_getTemplatePath() . '/Model.php';
        $realyModelPath = $this->_modelPathHttp . '/' . $params['router']['model'] . '.php';

        if (file_exists($realyModelPath)) {
            $resultModel->setResultCode(-3);
            $resultModel->setResultMsg('Model已经存在');
            $resultModel->setResultData(['filePath' => $realyModelPath]);
            return $resultModel;
        }
        $modelTemplateContent = file_get_contents($modelFilePath);
        if ($params['returnType'] == \Rpc\RpchttpModel::DATATYPE_PHP) {
            $returnType = 'self::DATATYPE_PHP';
        } elseif ($params['returnType'] == \Rpc\RpchttpModel::DATATYPE_STRING) {
            $returnType = 'self::DATATYPE_STRING';
        } elseif ($params['returnType'] == \Rpc\RpchttpModel::DATATYPE_XML) {
            $returnType = 'self::DATATYPE_XML';
        } else {
            $returnType = 'self::DATATYPE_JSON';
        }
        $keyErrorCode = strtolower($params['returnKeyCode']) == 'false'
            ? $params['returnKeyCode'] : "'{$params['returnKeyCode']}'";
        $keyErrorSval = is_numeric($params['returnCodeSucess'])
            ? $params['returnCodeSucess'] : "'{$params['returnCodeSucess']}'";
        $modelTemplateContentNew = str_replace(
            [
                '{DATE}', '{TIME}',
                '{MODEL_NAME}', '{RETURN_TYPE}',
                '{KEY_ERROR_CODE}', '{CODE_SUCCESS_VAL}',
                '{KEY_ERROR_MSG}', '{KEY_DATA_DATA}'
            ],
            [
                date('Y/m/d'), date('H:i'),
                $params['router']['model'], $returnType,
                $keyErrorCode, $keyErrorSval,
                $params['returnKeyMsg'], $params['returnKeyResult']
            ],
            $modelTemplateContent
        );
        $resultModel->setResultData(
            [
                'type' => 1,
                'content' => $modelTemplateContentNew,
                'filePath' => $realyModelPath
            ]
        );
        return $resultModel;
    }

    protected function _getTemplatePath()
    {
        if ($this->_templatePath === null) {
            $this->_templatePath = APPLICATION_PATH_APP . '/template/rpc/http';
        }
        return $this->_templatePath;
    }
}