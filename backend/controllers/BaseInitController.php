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

        // 1.操作日志数据初始化
        ActionLog::add('您的店铺初始化完成，开始您的奇幻之旅吧！','初始化');

        // 2.快捷菜单信息
        $quick_start_menu = [];
        array_push($quick_start_menu,
            [
                'avatar' => 'anticon anticon-wechat',
                'title' => '客户管理',
                'desc' => '查看微信端客户信息',
                'route' => '/customer/mgr'
            ]);
        array_push($quick_start_menu,
            [
                'avatar' => 'anticon anticon-file',
                'title' => '操作日志',
                'desc' => '查看重要操作日志',
                'route' => '/setting/action-log'
            ]);
        array_push($quick_start_menu,
            [
                'avatar' => 'anticon anticon-user',
                'title' => '员工管理',
                'desc' => '管理店铺员工，设置相关权限等',
                'route' => '/setting/user'
            ]);
        array_push($quick_start_menu,
            [
                'avatar' => 'anticon anticon-setting',
                'title' => '微信设置',
                'desc' => '设置相关参数，包括店铺设置，微信设置，体验者设置等',
                'route' => '/setting/setting/wechat'
            ]);
        $config = new Config();
        $config->symbol = 'by_quick_start_menu';
        $config->content = serialize($quick_start_menu); // 1:初始化完成
        $config->encode = 2;
        if(!$config->save()){
            \Yii::error($config->errors,'快捷菜单初始化数据失败');
        }

//        $config = new Config();
        $config->symbol = 'init';
        $config->content = '1'; // 1:初始化完成
        $config->encode = 3;
        if(!$config->save()){
            \Yii::error($config->errors,'初始化数据失败');
        }


    }
}