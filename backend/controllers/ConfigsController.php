<?php
/**
 * 设置相关控制器
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/7/18
 * Time: 上午9:50
 */

namespace baiyou\backend\controllers;


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
    public function actionSetAppletSecret(){
        $sid = Helper::getSid();
        $data=Yii::$app->request->post();
        $instance=Instance::findOne($sid);
        if($instance->load($data,'') && $instance->save()){
            $instance->online_qrcode = Wechat::getWechatQrCode($sid);
            $instance->save(); // 更新小程序码，代码略有冗余
            $instance->experience_qrcode = Yii::$app->params['admin_url'].'/'.$instance->experience_qrcode; // 体验版二维码，存在总后台的后端
            $instance->online_qrcode = Yii::$app->request->hostInfo.'/'.'/'.$instance->online_qrcode; // 上线后二维码,存在具体应用的后端
            return ["code"=>1,"message"=>"设置秘钥成功",'data' => $instance];
        }else{
            return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"参数错误","data"=>$instance->errors];
        }
    }
}