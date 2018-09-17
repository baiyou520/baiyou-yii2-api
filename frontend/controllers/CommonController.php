<?php
/**
 * 微信端通用控制器
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/8/17
 * Time: 下午2:02
 */

namespace baiyou\frontend\controllers;


use baiyou\backend\models\Category;
use baiyou\common\components\BaseErrorCode;
use baiyou\common\components\Helper;
use baiyou\common\components\Wechat;
use baiyou\common\models\Customer;

class CommonController extends BaseController
{
    public $modelClass = '';

    /**
     * 图片上传接口,将图片统一上传至图片服务器，并在应用服务器的media表中记录，未来再迁移到七牛云
     * 单图和多图上传使用同一个接口
     * @return array|string
     * @author  billyshen 2018/5/30 下午5:21
     */

    public function actionUploadImgs($id){
        $pic_group=Category::findOne(['symbol'=>'wechat_uploaded_pic']);
        if(empty($pic_group)){
            $pic_group = new Category();
            $pic_group->symbol = 'wechat_uploaded_pic';
            $pic_group->name = '微信端上传图片';
            $pic_group->save();
        }

        if(empty($pic_group)){
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"分组不存在"];
        }

        $medias = Helper::uploadImgs($pic_group->category_id);
        if (empty($medias)){
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"上传失败"];
        }else{
            return ["code"=>1,"message"=>"上传成功",'data' => $medias];
        }
    }

    /**
     * 获取我的推广码
     * @param $id
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/9/10 下午2:13
     */
    public function actionGetMyDistributionCode(){
        $customer_id=\Yii::$app->user->id;
        $customer=Customer::findOne($customer_id);
        if(empty($customer)){
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"用户不存在"];
        }
        $sid = Helper::getSid();
        $qr = Wechat::getWechatQrCodeUnlimited($sid,"pages/home/home","parentId=".$customer_id);
        if ($qr !== ''){
            return ["code"=>1,"message"=>"获取我的推广码成功","data"=>$qr];
        }else{
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"获取我的推广码失败"];
        }
    }
}