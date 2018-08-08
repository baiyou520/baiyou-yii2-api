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
    const KeyCode = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_$';
    /**
     * 随机字符串
     * @param int $length
     * @return string
     * @author  billyshen 2018/6/7 下午5:36
     */
    public static function randomString($length = 8)
    {
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
     * url请求的通用版本,当内容为空时,默认进行get请求,有内容时,进行post请求; 输入默认格式化，输出可选择
     * @param $url
     * @param null $data
     * @param bool $is_decode 小程序码等文件流直接返回数据即可
     * @return mixed
     * @author sft@caiyoudata.com
     * @time   2018/7/18 下午4:08
     */
    public static function https_request($url, $data = null,$is_decode = true){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen(json_encode($data))
                )
            );
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);

        if($error = curl_error($curl)){ // 返回一条最近一次cURL操作明确的文本的错误信息。
            \Yii::error($error,'curl请求错误');
        };
        curl_close($curl);

        if ($is_decode){
            return json_decode($output,true);
        } else {
            return $output; // 小程序码等文件流直接返回数据即可
        }

    }

    /**发送验证码
     * @param $phone
     * @param $msg
     * @return mixed
     * @author xuy@caiyoudata.com
     * @time  2018/6/25 15:21
     */

    public static function send_msg($phone,$msg){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");

        curl_setopt($ch, CURLOPT_HTTP_VERSION  , CURL_HTTP_VERSION_1_0 );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD  , 'api:key-76b6e20f3c5fdc259204d174267ac2e4');

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $phone,'message' =>$msg.'【百优应用】'));
        $res = curl_exec( $ch );
        curl_close( $ch );
        return $res;
    }

    /**
     * 给图片添加中台域名
     * @param $data
     * @param array $field 待加域名的 字段集合
     * @param string $host ,域名
     * @return mixed
     * @author nwh@caiyoudata.com
     * @time 2018/7/7 11:46
     */
    public static function add_host($data,$field=[]){
        //Yii::$app->request->hostInfo,可以获取当前域名
        $host="https://api-by-mall-web.baiyoudata.com";
        foreach($data as $key => &$value){
            if(is_array($value)){
                if(in_array((string)$key,$field)){
                    foreach ($value as &$val){
                        $val=$host.$val;
                    }
                }else{
                    $value=Helper::add_host($value,$field);
                }
            }else{
                if(in_array((string)$key,$field)&&!empty($value)&&$value!=' '){
                    $value=$host.$value;
                }
            }
        }
        return $data;
    }

    /**
     * 中断输出，用于调试
     * @param $data
     * @param bool $die
     * @author sft@caiyoudata.com
     * @time   2018/7/21 上午11:01
     */

    public static function p($data,$die = true){
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        if ($die){
            die();
        }
    }

    /**
     * 将10进制的数字字符串转为64进制的数字字符串
     * @param $m string 10进制的数字字符串
     * @param $len integer 返回字符串长度，如果长度不够用0填充，0为不填充
     * @return string
     */
    public static function hex10to64($m, $len = 0) {
        $KeyCode = Helper::KeyCode;
        $hex2 = decbin($m);
        $hex2 = Helper::str_rsplit($hex2, 6);
        $hex64 = array();
        foreach($hex2 as $one) {
            $t = bindec($one);
            $hex64[] = $KeyCode[$t];
        }
        $return = preg_replace('/^0*/', '', implode('', $hex64));
        if($len) {
            $clen = strlen($return);
            if($clen >= $len) {
                return $return;
            }
            else {
                return str_pad($return, $len, '0', STR_PAD_LEFT);
            }
        }
        return $return;
    }
    /**
     * 将16进制的数字字符串转为64进制的数字字符串
     * @param $m string 16进制的数字字符串
     * @param $len integer 返回字符串长度，如果长度不够用0填充，0为不填充
     * @return string
     */
    public static function hex16to64($m, $len = 0) {
        $KeyCode = Helper::KeyCode;
        $hex2 = array();
        for($i = 0, $j = strlen($m); $i < $j; ++$i) {
            $hex2[] = str_pad(base_convert($m[$i], 16, 2), 4, '0', STR_PAD_LEFT);
        }
        $hex2 = implode('', $hex2);
        $hex2 = Helper::str_rsplit($hex2, 6);
        foreach($hex2 as $one) {
            $hex64[] = $KeyCode[bindec($one)];
        }
        $return = preg_replace('/^0*/', '', implode('', $hex64));
        if($len) {
            $clen = strlen($return);
            if($clen >= $len) {
                return $return;
            }
            else {
                return str_pad($return, $len, '0', STR_PAD_LEFT);
            }
        }
        return $return;
    }

    /**
     * 功能和PHP原生函数str_split接近，只是从尾部开始计数切割
     * @param $str string 需要切割的字符串
     * @param $len integer 每段字符串的长度
     * @return array | bool
     */
    public static function str_rsplit($str, $len = 1) {
        if($str == null || $str == false || $str == '') return false;
        $strlen = strlen($str);
        if($strlen <= $len) return array($str);
        $headlen = $strlen % $len;
        if($headlen == 0) {
            return str_split($str, $len);
        }
        $return = array(substr($str, 0, $headlen));
        return array_merge($return, str_split(substr($str, $headlen), $len));
    }
}