<?php
/**
 * 工作台控制器，设置一些快捷操作，或者部分数据统计报表等
 * User: billyshen
 * Date: 2018/5/30
 * Time: 上午11:22
 */

namespace baiyou\backend\controllers;
use baiyou\backend\models\User;

class DashboardController extends BaseController
{
    public $modelClass = 'baiyou\backend\models\User';

    public function actions()
    {
        $actions = parent::actions();

        // 禁用动作
        unset($actions['index']);
        unset($actions['delete']);
        unset($actions['create']);
        return $actions;
    }
    // 工作台
    public function actionIndex(){
        \Yii::error('sss','ddd');

        $user_total = User::find()->count();// 用户总数
        $user_new_in_month = User::find()->count();// 本月新增用户数

        // 统计
        $statistics = [];
        array_push($statistics,['title' => '用户总数','count' => $user_total,'class' => 'bg-primary']);
        array_push($statistics,['title' => '本月新增用户数','count' => $user_new_in_month,'class' => 'bg-success']);

        $data = ['statistics' => $statistics];// 统计
        return  ['message' => 'ok','code' => 1,'data' => $data];
    }

    // 通知 消息 代办
    public function actionNotice(){

        // 统计
        $data = [];
        array_push($data,['id' => '000000001','avatar' =>  'https://gw.alipayobjects.com/zos/rmsportal/ThXAXghbEsBCCSDihZxY.png',
            'title' => 'bg-primary','datetime' => '2017-08-09','type' => '通知']);
        array_push($data,['id' => '000000002','avatar' =>  'https://gw.alipayobjects.com/zos/rmsportal/ThXAXghbEsBCCSDihZxY.png',
            'title' => 'bg-primary','datetime' => '2017-08-09','type' => '待办']);
        array_push($data,['id' => '000000002','avatar' =>  'https://gw.alipayobjects.com/zos/rmsportal/ThXAXghbEsBCCSDihZxY.png',
            'title' => 'bg-primary','description' => '22222','datetime' => '2017-08-09','type' => '消息']);

        return  ['message' => 'ok','code' => 1,'data' => $data];
    }
}