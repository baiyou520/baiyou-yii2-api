<?php
/**
 * 与微信相关的代码
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/7/18
 * Time: 下午1:52
 */

namespace baiyou\common\components;


use baiyou\common\models\Instance;
use CURLFile;
use Yii;

class Wechat
{
    /**
     * 用id和秘钥换取access_token
     * @param $sid
     * @return mixed
     * @author sft@caiyoudata.com
     * @time   2018/7/18 下午1:57
     */
    public static function getWechatAccessToken($sid){
        $cache=Yii::$app->cache;
        $wx_access_token = $cache->get('wx_access_token_'.$sid);
        if(empty($wx_access_token)){
            $instance = Instance::findOne($sid);
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$instance->applet_appid&secret=$instance->applet_appsecret";
            $result=Helper::https_request($url);

            if (isset($result['errcode'])){
                Yii::error($result,'获取公众平台的 API 调用所需的access_token失败');
            }else{
                $wx_access_token = $result['access_token'];
                $cache->set("wx_access_token_".$sid,$wx_access_token,$result['expires_in'] - 100); // 提前100秒刷新token
            }
        }
        return $wx_access_token;
    }

    /**
     * 获取小程序码，有限次数版本  A接口，生成小程序码，可接受path参数较长，生成个数受限。
     * @param $sid
     * @return mixed
     * @author sft@caiyoudata.com
     * @time   2018/7/18 下午2:19
     */
    public static function getWechatQrCode($sid){

        $instance = Instance::findOne($sid);
        $wx_access_token = Wechat::getWechatAccessToken($sid);
//        $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$wx_access_token;
        $url="https://api.weixin.qq.com/wxa/getwxacode?access_token=".$wx_access_token;
        $data=[
            "path"=> 'pages/home/home'
        ];

        $result=Helper::https_request($url,$data,false); // 第三个参数:小程序码等文件流直接返回数据即可

        if (isset(json_decode($result,true)['errcode'])){
            Yii::error($result,'获取小程序码失败');
            return '';
        }else{
            $dir = 'uploads/img/qrcode/';
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $destination = $dir.$instance->name.'(小程序码).png';
            $file = fopen($destination,"w+");
            fputs($file,$result);//写入文件
            fclose($file);
            return $destination;
        }
    }

    /**
     * 取小程序码，无限次数版本  B接口，生成小程序码，可接受页面参数较短，生成个数不受限。
     * @param $sid
     * @return string
     * @author sft@caiyoudata.com
     * @time   2018/8/31 下午7:35
     */
    public static function getWechatQrCodeUnlimited($sid,$page,$scene){

        $wx_access_token = Wechat::getWechatAccessToken($sid);
        $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$wx_access_token;
        $data=[
            "page" => $page,
            "scene"=> $scene
        ];

        $result=Helper::https_request($url,$data,false); // 第三个参数:小程序码等文件流直接返回数据即可
        if (isset($result['errcode'])){
            Yii::error($result,'获取小程序码失败');
            return '';
        }else{
            return $result; // 直接返回图片流
//            return 'data:image/jpeg;base64,'.base64_encode($result); // 以base64格式返回
        }
    }

    /**
     * 获取小程序二维码 接口C：适用于需要的码数量较少的业务场景
     * @param $sid
     * @param $path
     * @param $scene
     * @return mixed|string
     * @author sft@caiyoudata.com
     * @time   2018/9/5 下午3:04
     */
    public static function getWechatCodeLimited($sid,$path){

        $wx_access_token = Wechat::getWechatAccessToken($sid);
        $url="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$wx_access_token;
        $data=[
            "path" => $path,
            "width"=> 430
        ];

        $result=Helper::https_request($url,$data,false); // 第三个参数:小程序码等文件流直接返回数据即可
        if (isset($result['errcode'])){
            Yii::error($result,'获取小程序二维码失败');
            return '';
        }else{
            return $result;
        }
    }

    /**
     * 新增临时素材 用于用户发送客服消息或被动回复用户消息
     * @param $sid
     * @param $path
     * @return mixed|string
     * @author sft@caiyoudata.com
     * @time   2018/9/14 下午1:42
     */
    public static function uploadTempMedia($sid,$imgUrl){
        $wx_access_token = Wechat::getWechatAccessToken($sid);
        $url="https://api.weixin.qq.com/cgi-bin/media/upload?access_token=".$wx_access_token."&type=image";
        $data = array( 'media'=>$imgUrl );
        $ch1 = curl_init();
        $timeout = 10;
        curl_setopt ( $ch1, CURLOPT_URL, $url );
        curl_setopt ( $ch1, CURLOPT_POST, 1 );
        curl_setopt ( $ch1, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch1, CURLOPT_CONNECTTIMEOUT, $timeout );
        curl_setopt ( $ch1, CURLOPT_SAFE_UPLOAD, true );
        curl_setopt ( $ch1, CURLOPT_POSTFIELDS, $data );
        $result=curl_exec($ch1);
        curl_close($ch1);
        return json_decode($result,true);
    }

    /**
     * 组合模板并添加至帐号下的个人模板库
     * @param $sid
     * @param $keyword_id_list
     * @return mixed|string
     * @author sft@caiyoudata.com
     * @time  adt
     */
    public static function addTemplateMessage($sid,$at_id,$keyword_id_list){

        $wx_access_token = Wechat::getWechatAccessToken($sid);
        $url="https://api.weixin.qq.com/cgi-bin/wxopen/template/add?access_token=".$wx_access_token;
        $data=[
            "id" => $at_id,
            "keyword_id_list"=> $keyword_id_list
        ];

        $result=Helper::https_request($url,$data);
        if ($result['errcode'] === 0){
            return $result;
        }else {
            Yii::error($result, '组合模板并添加至帐号下的个人模板库失败');
            return $result;
        }
    }

    /**
     * 获取模板库某个模板标题下关键词库
     * @param $sid
     * @param $at_id
     * @return mixed
     * @author sft@caiyoudata.com
     * @time  adt
     */
    public static function getTplKeywordsId($sid,$at_id){

        $wx_access_token = Wechat::getWechatAccessToken($sid);
        $url="https://api.weixin.qq.com/cgi-bin/wxopen/template/library/get?access_token=".$wx_access_token;
        $data=[
            "id" => $at_id,
        ];

        $result=Helper::https_request($url,$data);
        if ($result['errcode'] === 0){
            return $result;
        }else {
            Yii::error($result, '获取模板库某个模板标题下关键词库失败');
            return $result;
        }
    }

    /**
     * 发送模板消息
     * @param $at_id
     * @return mixed
     * @author sft@caiyoudata.com
     * @time   2018/9/20 下午5:16
     */
    public static function sendTemplateMessage($openid,$template_id,$page,$form_id,$data){
        $sid = Helper::getSid();
        $wx_access_token = Wechat::getWechatAccessToken($sid);
        $url="https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$wx_access_token;
        $data_send=[
            "touser"=>$openid,
            "template_id"=>$template_id,
            "page"=>$page,//"点击进入详情查看,不写就没有点击详情$data['url']",
            "form_id"=>$form_id,
            "data"=> $data
        ];
        $result=Helper::https_request($url,$data_send);
        if ($result['errcode'] === 0){
            return $result;
        }else {
            Yii::error($result, '发送模板消息失败');
            return $result;
        }
    }


//
//    /**
//     * 添加体验者
//     * @param $sid
//     * @return string
//     * @author sft@caiyoudata.com
//     * @time   2018/7/24 下午2:34
//     */
//    public static function addExpMember($sid,$wechat_id){
//        $wx_access_token = Wechat::getWechatAccessToken($sid);
//        $url="https://api.weixin.qq.com/wxa/bind_tester?access_token=".$wx_access_token;
//        $data=[
//            "wechatid"=> $wechat_id
//        ];
//
//        $result=Helper::https_request($url,$data);
//        if ($result['errcode'] === 0){
//            return $result['userstr'];
//        }else{
//            Yii::error($result,'添加体验者失败');
//            return false;
//        }
//    }
}