<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 15:44
 */
namespace Admin;

use Api\Globals\Defined;
use \Illuminate\Database\Capsule\Manager as DB;
use Common\ResultModel;

class AppsModel
{

    public function getInfo($appid)
    {
        $row = DB::table('app')->where('appid', '=', $appid)->first();
        return (array)$row;
    }

    public function applyList()
    {
        $rows = DB::table('app_apply')->where('isvalid', '=', '1')->where('status', '=', 0)->get();
        $returnData = array();
        foreach ($rows as $item) {
            $returnData[] = (array)$item;
        }
        return $returnData;

    }

    public function appApply($params)
    {
        $resultModel = new ResultModel();
        if (empty($params['appName'])) {
            $resultModel->setResultCode(-1);
            $resultModel->setResultMsg('应用名不能为空');
            return $resultModel;
        }
        $row = DB::table('app_apply')->where('appname', '=', $params['appName'])->first();
        if ($row) {
            $resultModel->setResultCode(-1);
            $resultModel->setResultMsg('已被申请');
            return $resultModel;
        }
        $dataObj = new \DateTime();
        $insertData = array(
            'appname' => $params['appName'],
            'status' => 0,
            'ctime' => $dataObj->format('Y-m-d H:i:s'),
            'mtime' => $dataObj->format('Y-m-d H:i:s'),
            'isvalid' => 1
        );
        DB::table('app_apply')->insert($insertData);
        return $resultModel;
    }

    public function review($applyId)
    {
        $resultModel = new ResultModel();
        $tableObj = DB::table('app_apply');
        $row = $tableObj->where('id', '=', $applyId)->where('status', '=', 0)->first();
        if (!$row) {
            $resultModel->setResultCode(-1);
            $resultModel->setResultMsg('申请记录不存在');
            return $resultModel;
        }
        $dateObj = new \DateTime();

        $updateData = array(
            'status' => 1,
            'mtime' => $dateObj->format('Y-m-d H:i:s')
        );
        DB::beginTransaction();
        try {
            $tableObj->update($updateData);
            $appData = array(
                'appid' => uniqid(),
                'applyid' => $applyId,
                'appname' => $row->appname,
                'applyuser' => $row->applyuser,
                'applydate' => $row->ctime,
                'ctime' => $dateObj->format('Y-m-d H:i:s'),
                'mtime' => $dateObj->format('Y-m-d H:i:s'),
                'isvalid' => 1,
            );
            $appData['secret'] = sha1($applyId . $appData['appname'] . microtime(true));
            DB::table('app')->insert($appData);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $resultModel->setResultCode(-1);
            $resultModel->setResultMsg($e->getMessage());
        }
        return $resultModel;
    }

    public function appList()
    {
        $rows = DB::table('app')->where('isvalid', '=', '1')->get();
        $returnData = array();
        foreach ($rows as $item) {
            $returnData[] = (array)$item;
        }
        return $returnData;

    }

    public function getService($appid)
    {
        $row = DB::table('auth_resource')->select('apiid')->where('appid', '=', $appid)->get();
        $returnData = array();
        foreach ($row as $item) {
            $returnData[] = $item->apiid;
        }
        return $returnData;
    }

    public function addService($params)
    {
        $resultModel = new ResultModel();
        if (empty($params['appid'])) {
            $resultModel->setResultCode(-1);
            $resultModel->setResultMsg('参数传递错误');
            return $resultModel;
        }
        $row = $this->getInfo($params['appid']);
        if (!$row) {
            $resultModel->setResultCode(-1);
            $resultModel->setResultMsg('应用不存在');
            return $resultModel;
        }
        $hasService = $this->getService($params['appid']);
        if (!isset($params['apiid'])) {
            $apiidArr = array();
        } else {
            $apiidArr = (array)$params['apiid'];
        }
        $delService = array_diff($hasService, $apiidArr);
        $addService = array_diff($apiidArr, $hasService);
        if (!empty($delService)) {
            $flag = DB::table('auth_resource')->where('appid', '=', $params['appid'])
                ->whereIn('apiid', $delService)
                ->delete();
        }
        $insertData = array();
        $dateObj = new \DateTime();
        if (!empty($addService)) {
            foreach ($addService as $apiid) {
                $insertData[] = array(
                    'appid' => $params['appid'],
                    'apiid' => $apiid,
                    'isvalid' => 1,
                    'ctime' => $dateObj->format('Y-m-d H:i:s'),
                    'mtime' => $dateObj->format('Y-m-d H:i:s'),
                );
            }
            if (!empty($insertData)) {
                DB::table('auth_resource')->insert($insertData);
            }
        }
        return $resultModel;
    }

    public function bindThirdList($appid)
    {
        $row = DB::table('auth_third_bind')
            ->select(array('type', 'third_name', 'third_pwd', 'id'))
            ->where('appid', '=', $appid)->get()->toArray();
        if (empty($row)) {
            return $row;
        }
        $returnArray = array();
        foreach ($row as $item) {
            $returnArray[] = (array)$item;
        }
        return $returnArray;
    }

    public function addBindThird($param)
    {
        $resultModel = new ResultModel();
        $appid = $param['appid'];
        if (empty($appid)) {
            $resultModel->setResultCode(-1);
            $resultModel->setREsultMsg('已存在');
            return $resultModel;
        }
        $type = $param['type'];
        if (!in_array($type, Defined::getOtherAuthArray())) {
            $resultModel->setResultCode(-1);
            $resultModel->setREsultMsg('认证方式不存在');
            return $resultModel;
        }
        $authid = $param['authid'];
        $secret = $param['authsecret'];
        $row = DB::table('auth_third_bind')->where('appid', '=', $appid)->where('type', '=', $type)->first();

        if ($row) {
            $resultModel->setResultCode(-1);
            $resultModel->setREsultMsg('已存在');
            return $resultModel;
        }
        $flag = DB::table('auth_third_bind')
            ->insert(
                array('type' => $type, 'appid' => $appid, 'third_name' => $authid, 'third_pwd' => $secret)
            );
        if (!$flag) {
            $resultModel->setResultCode(-1);
            $resultModel->setREsultMsg('失败');
            return $resultModel;
        }
        return $resultModel;
    }

    public function delBindThird($appid, $type)
    {
        $resultModel = new ResultModel();
        $flag = DB::table('auth_third_bind')
            ->where('appid', '=', $appid)
            ->where('type', '=', $type)
            ->delete();
        if (!$flag) {
            $resultModel->setResultCode(-1);
            $resultModel->setREsultMsg('失败');
            return $resultModel;
        }
        return $resultModel;
    }
}