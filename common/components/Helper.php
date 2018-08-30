<?php
/**
 * 全局方法
 * User: billyshen
 * Date: 2018/6/7
 * Time: 下午5:35
 */

namespace baiyou\common\components;
use baiyou\backend\models\Category;
use baiyou\backend\models\Media;
use yii;

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
        return (int)$sid;
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

    /*****************图片上传相关*******************/

    public static function uploadImgs($id){
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
        $image_length = is_array($_FILES['image']['tmp_name'])? count($_FILES['image']['tmp_name']):1; // php7.2升级问题https://blog.csdn.net/w670328683/article/details/79402373
        for($i=0; $i<$image_length; $i++)
        {
            // 本地保存
            $pic_rename = Helper::hex10to64(Yii::$app->user->id). Helper::hex16to64(uniqid(rand())).".jpg"; // 文件唯一名
            if ($image_length === 1){ // 单图上传单独处理
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
            $media->group_id = $id;
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
        if ($image_length === 1) // 单图上传单独处理
            $medias = $medias[0];
        return $medias;
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

    /**
     * 分类查找,子类以子级显示
     * @param $list array
     * @param int $root $root:初始父id,比如0
     * @param string $pid 父类的字段名
     * @param string $pk 字段名
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2018/6/29 17:39
     */
    public static function generateTree($list,$root = 0,$pid='pid',$pk = 'id')
    {
        $tree = array();
        $packData = array();
        foreach ($list as $data) {
            $packData[$data[$pk]] = $data;
        }
        foreach ($packData as $key => $val) {
            if ($val[$pid] == $root) {
                $tree[] = &$packData[$key];
            } else {
                $packData[$val[$pid]]['son'][] = &$packData[(int)$key];
            }
        }
        return $tree;
    }
}