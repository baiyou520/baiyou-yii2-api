<?php
/**
 * 工作台控制器，设置一些快捷操作，或者部分数据统计报表等
 * User: billyshen
 * Date: 2018/5/30
 * Time: 上午11:22
 */

namespace baiyou\backend\controllers;
use baiyou\backend\models\ActionLog;
use baiyou\backend\models\Config;
use baiyou\common\components\Helper;
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
        $user_new_in_past_24hours = Customer::find()
            ->where(['>', 'created_at', time()-60*60*24])
            ->andWhere(['=', 'sid', Helper::getSid()])->count();// 过去24小时新增用户数

        // 统计
        $statistics = [];
        array_push($statistics,['title' => '今天新增用户数','count' => $user_new_in_past_24hours,'class' => 'bg-success']);
        array_push($statistics,['title' => '用户总数','count' => $user_total,'class' => 'bg-primary']);

        // 快捷菜单
        $quick_start_menus = Config::findOne(['symbol' => 'by_quick_start_menu']);
//        if ($quick_start_menus)
            $quick_start_menus = json_decode($quick_start_menus->content);
//        else
//            $quick_start_menus = [];
//        $quick_start_menus_system = Config::find()
//            ->where(['symbol' => 'by_quick_start_menu'])
//            ->andWhere(['sid' => 0])
//            ->one();
//        $quick_start_menus_system = unserialize($quick_start_menus_system['content']);
//        $quick_start_menus = array_merge($quick_start_menus_custom,$quick_start_menus_system);

        // 新增客户
        $new_customers =  Customer::find()
            ->where(['>', 'created_at', time()-60*60*24])
            ->andWhere(['=', 'sid', Helper::getSid()])->all();

         // 动态
        $activities = ActionLog::find()
            ->andWhere(['>', 'created_at', time()-60*60*24*7])
            ->andWhere(['=', 'status', 1])
            ->orderBy('created_at desc')->all(); // 先取过去七天的重要操作日志，参考https://help.youzan.com/displaylist/detail_4_11697
//
        $data = [
            'statistics' => $statistics,
            'quick_start_menus' => $quick_start_menus,
            'new_customers' => $new_customers,
            'activities' => $activities,
        ];
//        Helper::p($data);
        return  ['message' => 'ok','code' => 1,'data' => $data];
    }

    // 通知 消息 代办
    public function actionNotice(){
        $activities = ActionLog::find()
            ->andWhere(['>', 'created_at', time()-60*60*24*7])
            ->andWhere(['=', 'status', 1])
            ->orderBy('created_at desc')->limit(10)->all();

        $activities = \yii\helpers\ArrayHelper::toArray($activities);
//        Helper::p($activities);
        $data = [];
//        array_push($data,['id' => '000000001','avatar' =>  'https://gw.alipayobjects.com/zos/rmsportal/ThXAXghbEsBCCSDihZxY.png',
//            'title' => 'bg-primary','datetime' => '2017-08-09','type' => '通知']);
//        array_push($data,['id' => '000000002','avatar' =>  'https://gw.alipayobjects.com/zos/rmsportal/ThXAXghbEsBCCSDihZxY.png',
//            'title' => 'bg-primary','datetime' => '2017-08-09','type' => '待办']);
        foreach ($activities as $item){
            $act['type'] = '消息';
            $act['title'] = $item['name'];
            $act['description'] = $item['message'];
            $act['datetime'] = $item['created_at'];

            $data[] = $act;
        }
//        array_push($data,['id' => '000000002','avatar' =>  'https://gw.alipayobjects.com/zos/rmsportal/ThXAXghbEsBCCSDihZxY.png',
//            'title' => 'bg-primary','description' => '22222','datetime' => '2017-08-09','type' => '消息']);
        $data = ['list' => $data,'total'=>count($data)];
        return  ['message' => 'ok','code' => 1,'data' => $data];
    }
}