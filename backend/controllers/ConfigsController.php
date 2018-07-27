<?php
/**
 * 设置相关控制器
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/7/18
 * Time: 上午9:50
 */

namespace baiyou\backend\controllers;


use baiyou\backend\models\Experiencer;
use baiyou\common\components\BaseErrorCode;
use baiyou\common\components\Helper;
use baiyou\common\components\Wechat;
use baiyou\common\models\Instance;
use Yii;

class ConfigsController extends BaseController
{
    public $modelClass = 'baiyou\backend\models\Config';

    public function actions()
    {
        $actions = parent::actions();

        return $actions;
    }

    /**
     * 设置小程序秘钥
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/7/23 下午7:42
     */
    public function actionSetAppletSecret(){
        $sid = Helper::getSid();
        $data=Yii::$app->request->post();
        $instance=Instance::findOne($sid);
        if($instance->load($data,'') && $instance->save()){
            if (isset($data['applet_appsecret'])){
                $instance->online_qrcode = Wechat::getWechatQrCode($sid);
                $instance->save(); // 更新小程序码，代码略有冗余
            }
            $instance->experience_qrcode = Yii::$app->params['admin_url'].'/'.$instance->experience_qrcode; // 体验版二维码，存在总后台的后端
            $instance->online_qrcode = Yii::$app->request->hostInfo.'/'.$instance->online_qrcode; // 上线后二维码,存在具体应用的后端
            return ["code"=>1,"message"=>"设置小程序秘钥成功",'data' => $instance];
        }else{
            return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"参数错误","data"=>$instance->errors];
        }
    }

    /**
     * 获得微信端设置
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/7/24 上午10:38
     */
    public function actionGetAppletSetting(){
        $sid = Helper::getSid();
        $instance=Instance::findOne($sid);
        $instance->experience_qrcode = Yii::$app->params['admin_url'].'/'.$instance->experience_qrcode; // 体验版二维码，存在总后台的后端
        $instance->online_qrcode = Yii::$app->request->hostInfo.'/'.$instance->online_qrcode;// 上线后二维码,存在具体应用的后端
        if($instance){
            return ["code"=>1,"message"=>"获得微信设置成功",'data' => $instance];
        }else{
            return ["code"=>BaseErrorCode::$OBJECT_NOT_FOUND,"message"=>"实例未找到"];
        }
    }

    /**
     * 获得微信端设置
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/7/24 上午10:38
     */
    public function actionGetExpMembers(){
        $exp = Experiencer::findAll([
            'status' =>1,
        ]);
        // 获取所有页面的数据项的总数
        $totalCount = count($exp);
        $data = ['list' => $exp,'pagination'=>['total' => $totalCount]];
        return  ['message' => '获取体验者成功','code' => 1,'data' => $data];
    }

    /**
     * 添加体验者
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/7/24 下午3:56
     */
    public function actionAddExpMember(){
        $sid = Helper::getSid();
        $data=Yii::$app->request->post();
        $url = Yii::$app->params['admin_url'].'/v1/open/setExpMember/'.$sid;
        $data_to_admin=[
            "wechat_id"=> $data['wechat_id'],
            "action"=> 1 //添加体验者
        ];
        $results = Helper::https_request($url,$data_to_admin);
        if ($results['code'] == 1){
            $data['userstr'] = $results['data']['userstr'];
            $data['status'] = 1;
            $exp= new Experiencer();
            if($exp->load($data,'') && $exp->save()){
                return ["code"=>1,"message"=>"添加体验者成功"];
            }else{
                return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"保存失败","data" => $exp->errors];
            }
        }else{
            return ["code"=>BaseErrorCode::$SET_EXPERIENCER_FAILED,"message"=>"添加体验者失败","data"=>$results];
        }

    }

    /**
     * 解绑体验者
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/7/24 下午7:39
     */
    public function actionUnbindExpMember($id){
        $sid = Helper::getSid();
        $exp = Experiencer::findOne($id);
        $url = Yii::$app->params['admin_url'].'/v1/open/setExpMember/'.$sid;
        $data=[
            "wechat_id"=> $exp->wechat_id,
            "action"=> 2 //解绑体验者
        ];
        $results = Helper::https_request($url,$data);
        if ($results['code'] == 1){
            $exp->status = 0;
            if($exp->save()){
                return ["code"=>1,"message"=>"解绑体验者成功"];
            }else{
                return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"保存失败","data" => $exp->errors];
            }

        }else{
            return ["code"=>BaseErrorCode::$SET_EXPERIENCER_FAILED,"message"=>"解绑体验者失败","data"=>$results];
        }

    }
}