<?php
/**
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/8/4
 * Time: 上午9:51
 */

namespace baiyou\backend\controllers;


use baiyou\backend\models\ActionLog;
use baiyou\backend\models\Config;

class BaseInitController
{
    public static function init(){
        ActionLog::add('您的店铺初始化完成，开始您的奇幻之旅吧！','初始化');
        $config = new Config();
        $config->symbol = 'init';
        $config->content = '1'; // 1:初始化完成
        $config->encode = 3;
        if(!$config->save()){
            \Yii::error($config->errors,'初始化保存失败');
        }
    }
}