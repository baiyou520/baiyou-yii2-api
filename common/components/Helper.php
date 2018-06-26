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

        if(empty($sid)){
            $sid=\Yii::$app->request->get('sid');
        }
        return $sid;
    }

    /**
     * 从cookies中得到实例名称sname
     * @return mixed
     * @author sft@caiyoudata.com
     * @time   2018/6/26 下午6:51
     */
    public static function getSname()
    {
        $cookies = \Yii::$app->request->cookies;
        $sid =  $cookies->getValue('sname');
        return $sid;
    }
}