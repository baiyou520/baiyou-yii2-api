<?php
/**
 * 工作台控制器，设置一些快捷操作，或者部分数据统计报表等
 * User: billyshen
 * Date: 2018/5/30
 * Time: 上午11:22
 */

namespace baiyou\backend\controllers;
use baiyou\backend\models\Config;
use baiyou\common\models\Customer;

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

    /**
     * @return array
     * @author sft@caiyoudata.com
     * @time  adt
     */
    public function actionIndex(){

        $user_total = Customer::find()->count();// 用户总数
        $user_new_in_past_24hours = Customer::find()->where(['>', 'created_at', time()-60*60*24])->count();// 过去24小时新增用户数

        // 统计
        $statistics = [];
        array_push($statistics,['title' => '今天新增用户数','count' => $user_new_in_past_24hours,'class' => 'bg-success']);
        array_push($statistics,['title' => '用户总数','count' => $user_total,'class' => 'bg-primary']);

        // 快捷菜单
        $quick_start_menus = Config::findOne(['symbol' => 'by_quick_start_menu']);
        $quick_start_menus = unserialize($quick_start_menus->content);

        $data = ['statistics' => $statistics,'quick_start_menus' => $quick_start_menus];
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