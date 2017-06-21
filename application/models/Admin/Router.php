<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/7
 * Time: 15:44
 */
namespace Admin;

use \Illuminate\Database\Capsule\Manager as DB;
use Common\ResultModel;

class RouterModel
{

    public function getList()
    {
        $rows = DB::table('router_map')->join('api', 'router_map.apiid', '=', 'api.id')
            ->select(
                array(
                    'router_map.router',
                    'router_map.routername',
                    'api.name',
                    'api.url',
                    'api.host',
                    'api.wiki',
                    'router_map.id',
                    'api.parameter'
                )
            )
            ->orderBy('isvalid', 'desc')->get();
        $returnData = array();
        foreach ($rows as $item) {
            $arr = (array)$item;
            if ($arr['routername']) {
                $arr['name'] = $arr['routername'];
            }
            unset($arr['routername']);
            $returnData[] = $arr;
        }
        return $returnData;

    }


    public function updateService($params)
    {
        $resultModel = new ResultModel();
        $resultModel->setResultMsg('操作成功');
        if (
            empty($params['module']) ||
            empty($params['controller']) ||
            empty($params['action']) ||
            empty($params['apiid'])
        ) {
            $resultModel->setResultCode(-1);
            $resultModel->setResultMsg('参数传入错误');
            return $resultModel;
        }

        $serviceModel = new ServiceModel();
        $row = $serviceModel->getInfo($params['apiid']);
        if (!$row) {
            $resultModel->setResultCode(-1);
            $resultModel->setResultMsg('参数传入错误');
            return $resultModel;
        }
        $module = strtolower(str_replace('/', '', $params['module']));
        $controller = strtolower(str_replace('/', '', $params['controller']));
        $action = strtolower(str_replace('/', '', $params['action']));
        $localRouter = $module . '/' . $controller . '/' . $action;

        $apiTable = DB::table('router_map')->where('router', '=', $localRouter);
        if (isset($params['id'])) {
            $apiTable->where('id', '=', $params['id']);
        }
        $row = $apiTable->first();
        if ($row) {
            $resultModel->setResultCode(-1);
            $resultModel->setResultMsg('路由已存在');
            return $resultModel;
        }
        $data = array(
            'router' => $localRouter,
            'routername' => $params['routername'],
            'apiid' => $params['apiid'],
            'isvalid' => 1,
            'mtime' => date('Y-m-d H:i:s')
        );

        if (isset($params['id'])) {
            $flag = Db::table('router_map')->where('id', '=', $params['id'])->update($data);
        } else {
            $data['ctime'] = date('Y-m-d H:i:s');
            $flag = DB::table('router_map')->insert($data);
        }
        if (!$flag) {
            $resultModel->setResultCode(-1);
            $resultModel->setResultMsg('操作失败');
        }
        return $resultModel;
    }

    public function del($id)
    {
        $resultModel = new ResultModel();
        $flag = Db::table('router_map')->where('id', '=', $id)->delete();
        if (!$flag) {
            $resultModel->setResultCode(-1);
            $resultModel->setResultMsg('操作失败');
        }
        return $resultModel;
    }

}