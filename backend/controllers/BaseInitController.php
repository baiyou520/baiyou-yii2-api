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
                    'avatar' => 'anticon anticon-setting',
                    'title' => '微信设置',
                    'desc' => '设置相关参数，包括店铺设置，微信设置，体验者设置等',
                    'route' => '/customer/setting'
                ]);
            array_push($quick_start_menu,
                [
                    'avatar' => 'anticon anticon-wechat',
                    'title' => '客服消息',
                    'desc' => '当客户想咨询问题时，设置一个欢迎语很重要',
                    'route' => '/customer/message'
                ]);
            array_push($quick_start_menu,
                [
                    'avatar' => 'anticon anticon-file',
                    'title' => '门店信息',
                    'desc' => '如果您有线下门店，那么设置下门店信息，能让您的客户快速找到您',
                    'route' => '/setting/store'
                ]);
            array_push($quick_start_menu,
                [
                    'avatar' => 'anticon anticon-user',
                    'title' => '员工管理',
                    'desc' => '管理店铺员工，设置相关权限等',
                    'route' => '/setting/user'
                ]);
            $quick_start_menu_config = new Config();
            $quick_start_menu_config->symbol = 'by_quick_start_menu';
            $quick_start_menu_config->content = json_encode($quick_start_menu,JSON_UNESCAPED_UNICODE);
            $quick_start_menu_config->encode = 2;
            if(!$quick_start_menu_config->save()){
                \Yii::error(json_encode($quick_start_menu_config->errors,JSON_UNESCAPED_UNICODE),'快捷菜单初始化数据失败');
            }
        }


        //3.添加图片默认未分组
        $catgory_pic = Category::findOne(['symbol' => 'pic_group']);
        if (empty($catgory_pic)){
            $catgory_pic=new Category();
            $catgory_pic->symbol='pic_group';
            $catgory_pic->name='未分组';
            if(!$catgory_pic->save()){
                \Yii::error(json_encode($catgory_pic->errors,JSON_UNESCAPED_UNICODE),"默认图片分组失败");
            }
        }

        //4. 添加示例模板消息
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
        //5. 视频添加未分组 category
        if(empty(Category::find()->andWhere(['symbol'=>'video_group'])->one())){
            $category_video=new Category();
            $category_video->symbol='video_group';
            $category_video->name='未分组';
            if(!$category_video->save()){
                \Yii::error(json_encode($category_video->errors,JSON_UNESCAPED_UNICODE),"初始化,默认视频分组失败");
            }
        }
        //6.添加导航
        $nav=Config::find()->where(['symbol'=>'navigation','sid'=>Helper::getSid()])->one();
        if(empty($nav)){
            $content=[
                [
                    'text'=>'首页',
                    'pagePath'=>'/pages/home/home',
                    'iconPath'=>'shouye1',
                    'selectedIconPath'=>'shouye_on1',
                    'link_name'=>'首页'
                ],
                [
                    'text'=>'分类',
                    'pagePath'=>'/pages/classify/classify',
                    'iconPath'=>'fenlei1',
                    'selectedIconPath'=>'fenlei_on',
                    'link_name'=>'分类'
                ],
                [
                    'text'=>'购物车',
                    'pagePath'=>'/pages/shop_cart/shop_cart',
                    'iconPath'=>'gouwuche1',
                    'selectedIconPath'=>'gouwuche_on',
                    'link_name'=>'购物车'
                ],
                [
                    'text'=>'我的',
                    'pagePath'=>'/pages/my/my',
                    'iconPath'=>'wode',
                    'selectedIconPath'=>'wode_on',
                    'link_name'=>'我的'
                ],
            ];
            $nav_config=new Config();
            $nav_config->symbol='navigation';
            $nav_config->encode = 2;
            $nav_config->content = json_encode($content,JSON_UNESCAPED_UNICODE);
            if(!$nav_config->save()){
                \Yii::error(json_encode($nav_config->errors,JSON_UNESCAPED_UNICODE),'初始化,店铺导航出错');
            }
        }
        //7 .店铺"用户中心"菜单
        $customer_center=Config::find()->where(['symbol'=>'customer_center','sid'=>Helper::getSid()])->one();
        if(empty($customer_center)){
            $content=[
                'header'=>[
                    'menu_name'=>'头部设置',
                    'is_show'=>1
                ],
                'property'=>[
                    'menu_name'=>'用户资产',
                    'is_show'=>0
                ],
                'order'=>[
                    'menu_name'=>'订单管理',
                    'is_show'=>1
                ],
                'distribution'=>[
                    'menu_name'=>'分销中心',
                    'is_show'=>0
                ],
                'menu'=>[
                    'menu_name'=>'菜单',
                    'is_show'=>1,
                    'type'=>1,
                    'style'=>1,
                    'detail'=>[
                        [
                            'is_show'=>1,
                            'name'=>'coupon',
                            'title'=>'我的优惠券'
                        ],
                        [
                            'is_show'=>1,
                            'name'=>'code',
                            'title'=>'我的优惠码'
                        ],
                        [
                            'is_show'=>1,
                            'name'=>'piecing',
                            'title'=>'我参与的拼团'
                        ],
                        [
                            'is_show'=>1,
                            'name'=>'address',
                            'title'=>'地址管理'
                        ],
                        [
                            'is_show'=>1,
                            'name'=>'spread',
                            'title'=>'我的推广码'
                        ],
                        [
                            'is_show'=>1,
                            'name'=>'distribution',
                            'title'=>'分销赚钱',
                            'message'=>'提示语'
                        ],
                        [
                            'is_show'=>1,
                            'name'=>'shop',
                            'title'=>'门店信息'
                        ],
                        [
                            'is_show'=>1,
                            'name'=>'wechat',
                            'title'=>'微信客服'
                        ]

                    ]
                ]

            ];
            $customer_center_config=new Config();
            $customer_center_config->symbol = 'customer_center';
            $customer_center_config->encode = 2;
            $customer_center_config->content=json_encode($content,JSON_UNESCAPED_UNICODE);
            if(!$customer_center_config->save()){
                \Yii::error(json_encode($customer_center_config->errors,JSON_UNESCAPED_UNICODE),'初始化,用户中心添加出错');
            }
        }

        //8 自动处理超时未处理的退款申请 默认关闭状态
        $automatically_overtime_refunds=Config::find()->where(['symbol'=>'auto_handle_refund','sid'=>Helper::getSid()])->one();
        if(empty($automatically_overtime_refunds)){
            $customer_center_config=new Config();
            $customer_center_config->symbol = 'auto_handle_refund';
            $customer_center_config->encode = 3;
            $customer_center_config->content='0';//0不自动处理,1自动处理
            if(!$customer_center_config->save()){
                \Yii::error(json_encode($customer_center_config->errors,JSON_UNESCAPED_UNICODE),'初始化,自动处理退款申请超时配置 出错');
            }
        }
    }
}