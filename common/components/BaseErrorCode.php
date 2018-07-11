<?php
/**
 * 基础错误码
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/7/10
 * Time: 下午4:01
 */

namespace baiyou\common\components;


class BaseErrorCode
{
    public static $PARAMS_ERROR = 1000; // 参数错误，data中返回 $model->errors
    public static $OBJECT_NOT_FOUND = 1001; // 对象未找到
    public static $SID_WRONG = 1002; // sid不对，跳回总控制台，重新点击进入控制台即可
    public static $OBJECT_ALREADY_EXIST = 1003; // 实例已经存在，比如name唯一
}