<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 13:19
 */
namespace Admin;

use \Illuminate\Database\Capsule\Manager as DB;
use Common\ResultModel;

class ServiceModel
{
    public function getInfo($id)
    {
        $rows = (DB::table('api')->where('id', '=', $id)->first());
        return $rows;
    }

    public function getList()
    {
        $rows = (DB::table('api')->orderBy('status', 'desc')->get());
        $returnData = array();
        foreach ($rows as $item) {
            $returnData[] = (array)$item;
        }
        return $returnData;
    }

    public function getAuthList()
    {
        $rows = (DB::table('api')->where('isauth', '=', '1')->orderBy('status', 'desc')->get());
        $returnData = array();
        foreach ($rows as $item) {
            $returnData[] = (array)$item;
        }
        return $returnData;
    }

    public function updateService($params)
    {
        $resultModel = new ResultModel();
        $resultModel->setResultMsg('操作成功');
        if (empty($params['serviceName'])) {
            $resultModel->setResultCode(-1);
            $resultModel->setResultMsg('参数传入错误');
            return $resultModel;
        }
        $params['serviceUrl'] = trim($params['serviceUrl']);
        if (!empty($params['serviceUrl'])) {
            $apiTable = DB::table('api')->where('url', '=', $params['serviceUrl']);
            if (isset($params['id'])) {
                $apiTable->where('id', '<>', $params['id']);
            }
            $row = $apiTable->first();
            if ($row) {
                $resultModel->setResultCode(-1);
                $resultModel->setResultMsg('服务已存在');
                return $resultModel;
            }
        }
        $groupsId = isset($params['serviceGroups']) ? intval($params['serviceGroups']) : 0;
        $data = array(
            'name' => $params['serviceName'],
            'url' => $params['serviceUrl'],
            'isauth' => $params['serviceAuth'],
            'host' => $params['serviceHost'],
            'groupid' => $groupsId,
            'wiki' => $params['wiki'],
            'desc' => $params['serviceDesc'],
            'mtime' => date('Y-m-d H:i:s')
        );
        $dataParamster = array();
        if (isset($params['funcParamskey'])) {
            foreach ($params['funcParamskey'] as $k => $val) {
                if (!isset($params['funcParamsValue'][$k])) {
                    continue;
                }
                $dataParamster[$val] = $params['funcParamsValue'][$k];
            }
        }
        $data['parameter'] = json_encode($dataParamster);
        if (isset($params['id'])) {
            $flag = Db::table('api')->where('id', '=', $params['id'])->update($data);
        } else {
            $data['ctime'] = date('Y-m-d H:i:s');
            $flag = DB::table('api')->insertGetId($data);
            $resultModel->setResultData($flag);
        }
        if (!$flag) {
            $resultModel->setResultCode(-1);
            $resultModel->setResultMsg('操作失败');
        }
        return $resultModel;
    }
}