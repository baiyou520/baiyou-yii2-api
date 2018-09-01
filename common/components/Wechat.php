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
        if (isset($result['errcode'])){
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
    public static function getWechatQrCodeUnlimited($sid,$path,$scene){

        $instance = Instance::findOne($sid);
        $wx_access_token = Wechat::getWechatAccessToken($sid);
        $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$wx_access_token;
        $data=[
            "path" => $path,
            "scene"=> $scene
        ];

        $result=Helper::https_request($url,$data,false); // 第三个参数:小程序码等文件流直接返回数据即可
        if (isset($result['errcode'])){
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