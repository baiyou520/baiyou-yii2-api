<?php
/**
 * 通用控制器，用于图片上传等通用接口
 * User: billyshen
 * Date: 2018/5/30
 * Time: 下午5:20
 */

namespace baiyou\backend\controllers;

use yii\db\Query;
use yii\data\ActiveDataProvider;
use yii;
use yii\web\HttpException;
class CommonController extends BaseController
{
    public $modelClass = '';

    /**
     * 头像上传接口
     * @return array|string
     * @author  billyshen 2018/5/30 下午5:21
     */
    public function actionUploadAvatar(){
        $dir = 'uploads/img/avatar/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        if(move_uploaded_file($_FILES['image']['tmp_name'],$dir.$_FILES['image']['name'])){
            $img_path='https://'.$_SERVER['HTTP_HOST'].Yii::getAlias('@web') .'/'. $dir.$_FILES['image']['name'];
            $type=$_FILES['image']['type'];
            $size_src32=$this->imgZoom($img_path,[400, 80],$type);//第二个参数,由大到小
            return $size_src32;
        }else{
            return ["message"=>"错误号 ".$_FILES['image']['error']."图片上传失败","code"=>10017];
        }
    }

    /**
     * 图片缩放
     * @param $img_path
     * @param $width
     * @param $type
     * @return string
     * @author  billyshen 2018/5/30 下午5:21
     */
    function imgZoom($img_path,$width,$type){//64/32/128/256
//        $width = [256, 128, 64, 32];//设置需要的图片宽度,
        switch($type){
            case 'image/jpeg':$src = imagecreatefromjpeg($img_path);break;//创建新图片资源
            case 'image/png':$src = imagecreatefromPNG($img_path);break;
            case 'image/gif':$src = imagecreatefromgif($img_path);break;
        }
        $size_src = getimagesize($img_path);//获取图片信息
        foreach ($width as $item) {
            $image = imagecreatetruecolor($item, $item);//设置新图片宽高
            imagecopyresampled($image, $src, 0, 0, 0, 0, $item, $item, $size_src['0'], $size_src['1']);//从原图片哪里开始缩放
            $time = time();
            $image_name= 'uploads/img/avatar/'.$time.'_'.$item;//缩放图片重命名
            switch($type){
                case 'image/jpeg':imagejpeg($image, $image_name.".jpg");$image_name='https://'.$_SERVER['HTTP_HOST'].'/'.$image_name.".jpg";break;
                case 'image/png':imagepng($image, $image_name.".png");$image_name='https://'.$_SERVER['HTTP_HOST'].'/'.$image_name.".png";break;
                case 'image/gif':imagegif($image, $image_name.".gif");$image_name='https://'.$_SERVER['HTTP_HOST'].'/'.$image_name.".gif";break;
            }
            imagedestroy($image);
        }
        imagedestroy($src);
        return $image_name;
    }
}