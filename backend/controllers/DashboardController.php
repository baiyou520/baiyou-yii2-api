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
use baiyou\backend\models\Notice;
use baiyou\common\components\BaseErrorCode;
use baiyou\common\components\Helper;
use baiyou\common\models\Customer;
use baiyou\common\models\Instance;
use yii;

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
        $sid = Helper::getSid();
        $user_total = Customer::find()->count();// 用户总数
        $user_new_in_past_24hours = Customer::find()
            ->where(['>', 'created_at', time()-60*60*24])
            ->andWhere(['=', 'sid', $sid])->count();// 过去24小时新增用户数

        // 统计
        $statistics = [];
        array_push($statistics,['title' => '今天新增用户数','count' => $user_new_in_past_24hours,'class' => 'bg-success']);
        array_push($statistics,['title' => '用户总数','count' => $user_total,'class' => 'bg-primary']);

        // 快捷菜单
        $quick_start_menus = Config::findOne(['symbol' => 'by_quick_start_menu']);
        $quick_start_menus = json_decode($quick_start_menus->content);


        // 新增客户
        $new_customers =  Customer::find()
            ->where(['>', 'created_at', time()-60*60*24])
            ->andWhere(['=', 'sid', $sid])->all();

         // 动态
        $activities = ActionLog::find()
            ->andWhere(['>', 'created_at', time()-60*60*24*7])
            ->andWhere(['=', 'status', 1])
            ->orderBy('created_at desc')->all(); // 先取过去七天的重要操作日志，参考https://help.youzan.com/displaylist/detail_4_11697
//


        // app 相关信息
        $instance = Instance::findOne($sid);
        $sub_title = '';
        $license = '';
        $expired_at = '';
        switch ($instance->status)
        {
            case 0:
                $sub_title = '欢迎使用'.Yii::$app->params['app-name'].',您的店铺为试用版，为不影响使用，请及时购买正式版！';
                $license = '试用版';
                $expired_at =  (int)(($instance->expired_at - time()) / 86400)  .'天后过期，请及时续费！';
                break;
            case 1:
                $sub_title = '欢迎回来，祝您生意欣荣！';
                $license = '正式版';
                $expired_at =  (int)(($instance->expired_at - time()) / 86400) .'天后过期，请及时续费！';
                break;
            case -1:
                $sub_title = '您的店铺已经打烊，您仍旧可进行部分操作，但客户无法交易，请及时续费！';
                $license = '已打烊';
                $expired_at = '已打烊，请及时续费！';
                break;
            default:
                break;
        }
        $qr_code_status = 0; // 出现立即绑定按钮
        if ($instance->is_bind == 1){
            $qr_code_status = 1; // 出现设置秘钥按钮
        }
        if ($instance->online_qrcode !== ''){
            $qr_code_status = 2; // 出现2个二维码
        }
        $app = [
            'app_name' => Yii::$app->params['app-name'],
            'sid' => $instance->sid,
            'instance_name' => $instance->name,
            'status' => $instance->status,
            'license' => $license, // 版本
            'expired_at' => $expired_at, // 多久过期
            'sub_title' => $sub_title,
            'instance_thumb' => $instance->thumb,
            'experience_qrcode' => Yii::$app->params['admin_url'].'/'.$instance->experience_qrcode, // 体验版二维码，存在总后台的后端
            'online_qrcode' => Yii::$app->request->hostInfo.'/'.$instance->online_qrcode,// 上线后二维码,存在具体应用的后端
            'qr_code_status' => $qr_code_status,// 如何显示微信小程序二维码区域
            'level' => $instance->level, // 购买版本，暂定
        ];

//        Helper::p($data);
        $data = [
            'statistics' => $statistics,
            'quick_start_menus' => $quick_start_menus,
            'new_customers' => $new_customers,
            'activities' => $activities,
            'app' => $app
        ];
        return  ['message' => 'ok','code' => 1,'data' => $data];
    }

    // 通知 消息 代办
    public function actionNotice(){
//        $activities = ActionLog::find()
//            ->andWhere(['>', 'created_at', time()-60*60*24*7])
//            ->andWhere(['=', 'status', 1])
//            ->orderBy('created_at desc')->limit(10)->all();
//
//        $activities = \yii\helpers\ArrayHelper::toArray($activities);
////        Helper::p($activities);
        $data = [];
////        array_push($data,['id' => '000000001','avatar' =>  'https://gw.alipayobjects.com/zos/rmsportal/ThXAXghbEsBCCSDihZxY.png',
////            'title' => 'bg-primary','datetime' => '2017-08-09','type' => '通知']);
////        array_push($data,['id' => '000000002','avatar' =>  'https://gw.alipayobjects.com/zos/rmsportal/ThXAXghbEsBCCSDihZxY.png',
////            'title' => 'bg-primary','datetime' => '2017-08-09','type' => '待办']);
//
//        // 消息
//        foreach ($activities as $item){
//            $act['type'] = '消息';
//            $act['title'] = $item['name'];
//            $act['description'] = $item['message'];
//            $act['datetime'] = $item['created_at'];
//            $data[] = $act;
//        }
//
        // 待办
        $todos = Notice::find()
            ->andWhere(['=', 'status', 0])
            ->orderBy('created_at asc')->all();

        foreach ($todos as $item){
            $passed_hours = round((time()-$item['created_at']/1000)/3600,2);
            if ($passed_hours < 2){
                $todo['status'] = 'processing';
            }
            if ($passed_hours >= 2 && $passed_hours <= 24){
                $todo['status'] = 'doing';
            }
            if ($passed_hours > 24){
                $todo['status'] = 'urgent';
            }
            switch ($item['type']) {
                case 1:
                    $todo['type'] = '店铺通知';
                    break;
                case 2:
                    $todo['type'] = '订单提醒';
                    break;
                case 3:
                    $todo['type'] = '维权待办';
                    break;
                case 4:
                    $todo['type'] = '提现提醒';
                    break;
            }
            $todo['notice_id'] = $item['notice_id'];
            $todo['title'] = $item['title'];
            $todo['description'] = $item['content'];
            $todo['extra'] = '已耗时'.$passed_hours .'H';
            $todo['related_id'] = $item['related_id'];
            $todo['related_route'] = $item['related_route'];
            $data[] = $todo;
        }
        $data = ['list' => $data,'total'=>count($data)];
        return  ['message' => 'ok','code' => 1,'data' => $data];
    }

    /**
     * 标记已读，可以多个一起标记
     * @return array
     * @author  billyshen 2018/6/21 下午2:33
     */
    public function actionMarkRead(){
        $request=\Yii::$app->request;
        $params=$request->post();

        $affected_rows = Notice::updateAll(['status' => 1],['in','notice_id',$params['notice_id']]);
        if($affected_rows > 0){
            return ["message"=>"操作成功",'code'=>1];
        }else{
            return ["message"=>"操作失败",'code'=>BaseErrorCode::$DELETE_DB_ERROR];
        }
    }

//    public function actionMarkRead(){
//        $request=\Yii::$app->request;
//        $params=$request->post();
//        if (isset($params['notice_id'])){
//            $affected_rows = Notice::updateAll(['status' => 1],['in','notice_id',$params['notice_id']]);
//        }else{
//            $affected_rows = Notice::updateAll(['status' => 1],['=','type',$params['type']]);
//        }
//
//        if($affected_rows > 0){
//            return ["message"=>"操作成功",'code'=>1];
//        }else{
//            return ["message"=>"操作失败",'code'=>BaseErrorCode::$DELETE_DB_ERROR];
//        }
//    }
}