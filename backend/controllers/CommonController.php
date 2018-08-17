<?php
/**
 * 通用控制器，用于图片上传等通用接口
 * User: billyshen
 * Date: 2018/5/30
 * Time: 下午5:20
 */

namespace baiyou\backend\controllers;

use baiyou\common\components\BaseErrorCode;
use baiyou\common\components\Helper;
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
        $medias = Helper::uploadImgs();
        if (empty($medias)){
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"上传失败"];
        }else{
            return ["code"=>1,"message"=>"上传成功",'data' => $medias];
        }
    }
}