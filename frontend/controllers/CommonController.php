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
use baiyou\backend\models\Config;
use baiyou\common\components\BaseErrorCode;
use baiyou\common\components\Helper;
use baiyou\common\components\Wechat;
use baiyou\common\models\Customer;
use yii;

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
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/9/10 下午2:13
     */
    public function actionGetMyDistributionCode(){
        $customer_id=\Yii::$app->user->id;
        $customer=Customer::find()->where(['id'=>$customer_id])->one();
        if(empty($customer)){
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"用户不存在"];
        }
        $sid = Helper::getSid();

        // 小程序分享码
        $qr = Wechat::getWechatQrCodeUnlimited($sid,"pages/home/home","pId=".$customer_id.'&sid='.$sid);
        $qr_code_name = $sid.'_'.$customer_id.'_'.'qr_code.jpg';
        file_put_contents($qr_code_name, $qr);
        
        // 连接图片服务器
        $ftp_server = Yii::$app->params['img_server']; //要连接的服务器域名
        $conn=ftp_connect($ftp_server['domain']); //连接FTP服务器
        ftp_login($conn,$ftp_server['ftpuser_name'],$ftp_server['ftpuser_passwd']) or die("Cannot login");; //发送用户名和密码
        ftp_set_option($conn, FTP_USEPASVADDRESS, false); // 解决路由无法到达的问题 https://stackoverflow.com/questions/38982901/php-ftp-passive-ftp-server-behind-nat
        ftp_pasv($conn, true); // 切换为被动模式

        // 上传小程序分享码
        ftp_put($conn,'avatars/'.$qr_code_name,$qr_code_name,FTP_BINARY);
        unlink($qr_code_name); // 删除应用服务器上的图片

        if(!empty($customer->avatar)){
            // 微信头像
            $img = file_get_contents($customer->avatar);
            $wx_avatar_name = $sid.'_'.$customer_id.'_'.'wx_avatar.jpg';
            file_put_contents($wx_avatar_name,$img);
            // 上传微信头像
            ftp_put($conn,'avatars/'.$wx_avatar_name,$wx_avatar_name,FTP_BINARY);
            unlink($wx_avatar_name); // 删除应用服务器上的图片
        }else{
            $wx_avatar_name='';
        }
         $data = [
             'fingerprint' => 'https://'.Yii::$app->params['img_server']['domain'].'/assets/fingerprint.png',
             'qr_code' => 'https://'.Yii::$app->params['img_server']['domain'].'/avatars/'.$qr_code_name,
             'wx_avatar' => empty($wx_avatar_name)?'':'https://'.Yii::$app->params['img_server']['domain'].'/avatars/'.$wx_avatar_name,
             'nickname'=>$customer['nickname']
        ];

        $configs=Config::findOne(['symbol'=>'dis_share_setting']);
        if (!empty($configs)){
            $content  = json_decode($configs->content,true);
            $data = array_merge($data,[
                'share_title' => isset($content['title'])?$content['title']:'',
                'share_image_url' => isset($content['imageUrl'])?$content['imageUrl']:''
            ]);
        }
        if ($qr !== ''){
            return ["code"=>1,"message"=>"获取我的推广码成功",'data'=> $data];
        }else{
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"获取我的推广码失败"];
        }
    }
}