<?php
/**
 * 通用控制器，用于图片上传等通用接口
 * User: billyshen
 * Date: 2018/5/30
 * Time: 下午5:20
 */

namespace baiyou\backend\controllers;

use baiyou\backend\models\Category;
use baiyou\backend\models\Media;
use baiyou\common\components\BaseErrorCode;
use baiyou\common\components\Helper;
use baiyou\common\models\Instance;
use yii\db\Exception;

class CommonController extends BaseController
{
    public $modelClass = '';

    /**
     * 图片上传接口,将图片统一上传至图片服务器，并在应用服务器的media表中记录，未来再迁移到七牛云
     * 单图和多图上传使用同一个接口 需要提供分组编号
     * @return array|string
     * @author  billyshen 2018/5/30 下午5:21
     */

    public function actionUploadImgs($id){

        $instance=Instance::findOne(Helper::getSid());
        if($instance['status']<0){
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"店铺已打烊,无法操作"];
        }

        $pic_group=Category::findOne($id);
        if(empty($pic_group)){
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"分组不存在"];
        }

        $medias = Helper::uploadImgs($id);
        if (empty($medias)){
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"上传失败"];
        }else{
            return ["code"=>1,"message"=>"上传成功",'data' => $medias];
        }
    }

    /**
     * 视频上传
     * @param $id
     * @return array|Media
     * @author nwh@caiyoudata.com
     * @time 2018/11/13 18:02
     */
    public function actionUploadVideo($id){
        $instance=Instance::findOne(Helper::getSid());
        if($instance['status']<0){
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"店铺已打烊,无法操作"];
        }
        try{
            //视频上传参数验证
            if(!isset($_FILES['file'])){
                return ['message'=>'参数错误','code'=>BaseErrorCode::$FAILED,'data'=>'文件上传参数name要等于file'];
            }
            $video_file=$_FILES['file'];
            $params=\Yii::$app->request->post();
            $cover_pic=isset($params['cover_pic'])?$params['cover_pic']:0;
            if(strtolower(substr($video_file['name'],-4))!='.mp4'){
                return ['message'=>'视频必须是mp4格式才可以','code'=>BaseErrorCode::$FAILED];
            }elseif(!empty($cover_pic)&&empty(Media::findOne($cover_pic))){
                return ['message'=>'视频封面图片未找到','code'=>BaseErrorCode::$FAILED];
            }elseif($_FILES['file']['size']==0||$_FILES['file']['size']>(1024*1024*1024)){//据测试size=0时，是上传视频过大，
                return ['message'=>'视频太大了','code'=>BaseErrorCode::$FAILED];
            }elseif(empty($id)||empty(Category::findOne($id))){
                return ['message'=>'视频分组id错误','code'=>BaseErrorCode::$FAILED];
            }

            // 连接图片服务器
            $ftp_server = \Yii::$app->params['img_server']; //要连接的服务器域名
            $conn=ftp_connect($ftp_server['domain']); //连接FTP服务器
            ftp_login($conn,$ftp_server['ftpuser_name'],$ftp_server['ftpuser_passwd']) or die("Cannot login");; //发送用户名和密码
            ftp_set_option($conn, FTP_USEPASVADDRESS, false); // 解决路由无法到达的问题 https://stackoverflow.com/questions/38982901/php-ftp-passive-ftp-server-behind-nat
            ftp_pasv($conn, true); // 切换为被动模式

            // 创建以年月日为区分的文件夹，便于日后分服务器
            $dir = date('Y').'/'.date('m').'/'.date('d');
            function ftp_mksubdirs($ftpcon, $ftpath) // 解决ftp_mkdir不支持多级创建文件夹问题
            {
                $parts = explode('/', $ftpath);
                foreach ($parts as $part) {
                    if (!@ftp_chdir($ftpcon, $part)) {
                        ftp_mkdir($ftpcon, $part);
                        ftp_chdir($ftpcon, $part);
                    }
                }
            }
            ftp_mksubdirs($conn,$dir);
            // 本地保存
            $video_rename = Helper::hex10to64(\Yii::$app->user->id). Helper::hex16to64(uniqid(rand())).".mp4"; // 文件唯一名
            $video_name = $video_file['name'];
            $size = (int)round($video_file['size']/(1024*1024),0); // 视频大小,M为单位,四舍五入
            move_uploaded_file($video_file['tmp_name'],$video_rename);

            // 上传到图片服务器相应文件夹
            $tempstate=ftp_put($conn,$video_rename,$video_rename,FTP_BINARY); //以二进制方式上传文件
            if(!$tempstate){
                return ["code"=>BaseErrorCode::$FAILED,"message"=>"上传失败，请检查配置"];
            }

            // 删除应用服务器上的图片
            unlink($video_rename);

            // 保存本地对应记录
            $media = new Media();

            $media->name = $video_name;
            $media->url = $dir.'/'.$video_rename;
            $media->type = 3;//表示视频
            $media->group_id = $params['group_id'];
            $media->height = $size;//视频大小
            $media->width = $cover_pic;//视频封面
            if(!$media->save()){
                \Yii::error($media->errors,'保存本地对应记录失败');
                return ['message'=>'视频保存失败','code'=>BaseErrorCode::$SAVE_DB_ERROR,'data'=>$media->errors];
            }
            return $media;
        }
        catch (Exception $e){
            \Yii::error(json_encode($e->errorInfo,JSON_UNESCAPED_UNICODE),'视频上传失败');
            return ['message'=>'上传失败','code'=>BaseErrorCode::$FAILED,'data'=>$e->errorInfo];
        }
    }
}