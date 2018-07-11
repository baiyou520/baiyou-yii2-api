<?php
/**
 * 全局方法
 * User: billyshen
 * Date: 2018/6/7
 * Time: 下午5:35
 */

namespace baiyou\common\components;


class Helper
{
    /**
     * 随机字符串
     * @param int $length
     * @return string
     * @author  billyshen 2018/6/7 下午5:36
     */
    public static function randomString($length = 8)
    {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $string ='';
        for ( $i = 0; $i < $length; $i++ )
        {
            $string .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $string;
    }

    /**
     * 从cookies中得到实例编号sid
     * @return mixed
     * @author sft@caiyoudata.com
     * @time   2018/6/26 下午3:40
     */
    public static function getSid()
    {
        $cookies = \Yii::$app->request->cookies;
        $sid =  $cookies->getValue('sid');

        // 如果是微信端访问的话，是没有cookies，这个时候需要微信端提供一个sid参数
        if(empty($sid)){
            $sid=\Yii::$app->request->get('sid');
        }
        return $sid;
    }
    /**
     * url请求的通用版本,
     * @param $url
     * @param null $data 当内容为空时,默认进行get请求,有内容时,进行post请求
     * @return mixed
     * @author nwh@caiyoudata.com
     * @time 2018/7/7 11:50
     */
    public static function https_request($url, $data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($data)
                )
            );
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return json_decode($output,true);
    }

}