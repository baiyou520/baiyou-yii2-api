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

    public function actionUploadImgs(){

        // 连接图片服务器
        $ftp_server = Yii::$app->params['img_server']; //要连接的服务器域名
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

        // 处理图片
        $medias = [];
        for($i=0; $i<count($_FILES['image']['tmp_name']); $i++)
        {
            // 本地保存
            $pic_rename = Helper::hex10to64(Yii::$app->user->id). Helper::hex16to64(uniqid(rand())).".jpg"; // 文件唯一名
            if (count($_FILES['image']['tmp_name']) === 1){ // 单图上传单独处理
                $pic_name = $_FILES['image']['name']; // 原始上传文件名
                move_uploaded_file($_FILES['image']['tmp_name'],$pic_rename);
            }else{
                $pic_name = $_FILES['image']['name'][$i];
                move_uploaded_file($_FILES['image']['tmp_name'][$i],$pic_rename);
            }

            // 上传到图片服务器相应文件夹
            $tempstate=ftp_put($conn,$pic_rename,$pic_rename,FTP_BINARY); //以二进制方式上传文件
            if(!$tempstate){
                return ["code"=>BaseErrorCode::$FAILED,"message"=>"上传失败，请检查配置"];
            }

            // 删除应用服务器上的图片
            unlink($pic_rename);

            // 保存本地对应记录
            $media = new Media();
            $media->name = $pic_name;
            $media->url = $dir.'/'.$pic_rename;
            $media->type = 1;
            $media->group_id = $this->group_id();
            if(!$media->save()){
                \Yii::error($media->errors,'保存本地对应记录失败');
            }
            $uploaded_media['url'] = 'https://'.Yii::$app->params['img_server']['domain'].'/'.$media->url;
            $uploaded_media['thumb_url'] = 'https://'.Yii::$app->params['img_server']['domain'].'/'.$media->url.
                Yii::$app->params['img_server']['default_thumb_size'];
            $uploaded_media['media_id'] = $media->media_id;
            $uploaded_media['name'] = $media->name;
            $uploaded_media['group_id'] = $media->group_id;
            $medias[]= $uploaded_media;
        }

        ftp_quit($conn);// 关闭联接,不然会一直开着占用资源
        if (count($_FILES['image']['tmp_name']) === 1) // 单图上传单独处理
            $medias = $medias[0];
        return ["code"=>1,"message"=>"上传成功",'data' => $medias];
    }

    /**
     * 获得默认分组id
     * @return int
     * @author sft@caiyoudata.com
     * @time   2018/8/6 下午4:54
     */
    private function group_id(){
        $pic_group=Category::find()->andWhere(['symbol'=>'pic_group'])->one();
        if(empty($pic_group)){
            $pic_group = new Category();
            $pic_group->symbol = 'pic_group';
            $pic_group->name = '无分组';
            $pic_group->sort = 1;
            $pic_group->save();
        }
        return $pic_group->category_id;
    }


}