<?php
/**
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/8/4
 * Time: 上午9:51
 */

namespace baiyou\backend\controllers;


use baiyou\backend\models\ActionLog;
use baiyou\backend\models\Category;
use baiyou\backend\models\Config;
use baiyou\common\components\Helper;

class BaseInitController
{
    public static function init(){

        // 1.操作日志数据初始化
//        ActionLog::add('您的店铺初始化完成，开始您的奇幻之旅吧！','初始化');
        //        // 初始化完成
//        $init_config = new Config();
//        $init_config->symbol = 'init';
//        $init_config->content = '1'; // 1:初始化完成
//        $init_config->encode = 3;
//        if(!$init_config->save()){
//            \Yii::error($init_config->errors,'初始化数据失败');
//        }

        // 2.快捷菜单信息
        $quick_start_menu_config = Config::findOne(['symbol' => 'by_quick_start_menu']);
        if (empty($quick_start_menu_config)){
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
                    'route' => '/customer/setting/wechat'
                ]);
            $quick_start_menu_config = new Config();
            $quick_start_menu_config->symbol = 'by_quick_start_menu';
            $quick_start_menu_config->content = json_encode($quick_start_menu,JSON_UNESCAPED_UNICODE);
            $quick_start_menu_config->encode = 2;
            if(!$quick_start_menu_config->save()){
                \Yii::error(json_encode($quick_start_menu_config->errors,JSON_UNESCAPED_UNICODE),'快捷菜单初始化数据失败');
            }
        }


        //添加图片默认未分组
        $catgory_pic = Config::findOne(['symbol' => 'by_quick_start_menu']);
        if (empty($catgory_pic)){
            $catgory_pic=new Category();
            $catgory_pic->symbol='pic_group';
            $catgory_pic->name='未分组';
            if(!$catgory_pic->save()){
                \Yii::error(json_encode($catgory_pic->errors,JSON_UNESCAPED_UNICODE),"默认图片分组失败");
            }
        }

        // 添加示例模板消息
        $template_message_config = Config::findOne(['symbol' => 'template_message']);
        if (empty($template_message_config)){
            $template_message = [];
            array_push($template_message,
                [
                    'name' => '流程待办提醒',
                    'tpl_id' => '',
                    'desc' => '流程标题、上步办理人、单据类型、内容概述、申请人、申请时间、申请人',
                    'at_id' => 'AT0082',
                    'keywords' => '[2,3,4]',
                ]);
            $template_message_config = new Config();
            $template_message_config->symbol = 'template_message';
            $template_message_config->content = json_encode($template_message,JSON_UNESCAPED_UNICODE);
            $template_message_config->encode = 2;
            if(!$template_message_config->save()){
                \Yii::error(json_encode($template_message_config->errors,JSON_UNESCAPED_UNICODE),'添加示例模板消息失败');
            }
        }
    }
}