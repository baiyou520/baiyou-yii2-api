<?php
/**
 * 全局方法
 * User: billyshen
 * Date: 2018/6/7
 * Time: 下午5:35
 */

namespace baiyou\common\frame;


class GlobalFunctions
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
}