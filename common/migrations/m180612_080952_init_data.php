<?php

use yii\db\Migration;

/**
 * 初始化各类数据，如菜单，角色，各类配置等
 * Class m180612_080952_init_data
 */
class m180612_080952_init_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1.添加路由 ,sid设置为0表示路由为系统默认
        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['/*', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/customers/index', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/customers/update', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/customers/view', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/dashboard/index', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/dashboard/notice', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/logs/index', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/logs/view', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/logs/get-action-log', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/create', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/delete', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/index', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/start-up', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/update', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/view', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/configs/set-applet-secret', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/configs/get-applet-setting', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/configs/get-exp-members', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/configs/add-exp-member', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/configs/unbind-exp-member', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/authorities/get-permissions-of-role', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/authorities/index', 0, 2, NULL, NULL, NULL, time(), time()],

            ]);

        /** 2.添加权限点,sid设置为0表示权限点为系统默认,权限点跟着菜单走
         * L0 代表group，所有应用规定为三个组，即L0概况组、L0应用组、L0系统组。 开发者自己开发的所有菜单和权限要放入L0应用组
         * L1 即一级菜单，比如Demo演示管理
         * L2 即二级菜单，比如Demo列表
         * L3 即三级权限，比如Demo演示新增、Demo演示修改等，注: 三级权限不是菜单，但仍旧需要设计权限点。比如高级管理员可以新增员工，普通管理只能查看员工
        */
        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['概况', 0, 2, 'L0概况组', NULL, NULL, time(), time()],
                    ['首页', 0, 2, 'L1首页，包括各类基础权限', NULL, NULL, time(), time()],

                ['应用', 0, 2, 'L0应用组', NULL, NULL, time(), time()],

                ['系统', 0, 2, 'L0系统组', NULL, NULL, time(), time()],
                    ['设置', 0, 2, 'L1设置管理', NULL, NULL, time(), time()],
                        ['错误日志', 0, 2, 'L2错误日志管理', NULL, NULL, time(), time()],
        //                ['操作日志', 0, 2, '操作日志管理', NULL, NULL, time(), time()],
                        ['员工管理', 0, 2, 'L2整个员工管理模块权限点集合', NULL, NULL, time(), time()],
                            ['员工修改', 0, 2, 'L3修改某个员工', NULL, NULL, time(), time()],
                            ['员工删除', 0, 2, 'L3删除某个员工', NULL, NULL, time(), time()],
                            ['员工新增', 0, 2, 'L3后台直接新增一个员工', NULL, NULL, time(), time()],
                            ['员工查看', 0, 2, 'L3查看中台管理端的员工', NULL, NULL, time(), time()],
                        ['微信设置', 0, 2, 'L2设置微信小程序相关信息', NULL, NULL, time(), time()],
                    ['客户管理', 0, 2, 'L2整个微信端客户管理权限', NULL, NULL, time(), time()],
                        ['客户修改', 0, 2, 'L3修改微信端客户资料', NULL, NULL, time(), time()],
                        ['客户查看', 0, 2, 'L3查看客户列表页及详情页', NULL, NULL, time(), time()],
            ]);
        // 2.1 设计权限点，安装菜单的层级关系设置
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['概况', '首页'],
                    ['首页', '/by/dashboard/index'],
                    ['首页', '/by/dashboard/notice'],
                    ['首页', '/by/users/start-up'],

                ['系统', '设置'],
                    ['设置', '错误日志'],
                        ['错误日志', '/by/logs/index'],
                        ['错误日志', '/by/logs/view'],
                //                ['操作日志', '/by/logs/get-action-log'],
                    ['设置', '员工管理'],
                        ['员工管理', '员工查看'],
                        ['员工管理', '员工修改'],
                        ['员工管理', '员工删除'],
                        ['员工管理', '员工新增'],
                            ['员工查看', '/by/users/index'],
                            ['员工查看', '/by/users/view'],
                            ['员工查看', '/by/authorities/index'],
                            ['员工查看', '/by/authorities/get-permissions-of-role'],
                            ['员工修改', '/by/users/update'],
                            ['员工新增', '/by/users/create'],
                            ['员工删除', '/by/users/delete'],
                    ['设置', '微信设置'],
                        ['微信设置', '/by/configs/set-applet-secret'],
                        ['微信设置', '/by/configs/get-applet-setting'],
                        ['微信设置', '/by/configs/get-exp-members'],
                        ['微信设置', '/by/configs/add-exp-member'],
                        ['微信设置', '/by/configs/unbind-exp-member'],
                ['系统', '客户管理'],
                    ['客户管理', '客户修改'],
                    ['客户管理', '客户查看'],
                        ['客户查看', '/by/customers/index'],
                        ['客户查看', '/by/customers/view'],
                        ['客户修改', '/by/customers/update'],

            ]);
        // 3.添加角色,sid设置为0表示这4个角色为系统默认
        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['admin', 0, 1, '普通管理员', NULL, NULL, time(), time()],
                ['super_admin', 0, 1, '高级管理员', NULL, NULL, time(), time()],
                ['root', 0, 1, '开发人员', NULL, NULL, time(), time()],
                ['user', 0, 1, '普通用户', NULL, NULL, time(), time()],
            ]);
        // 3.1 为角色添加权限
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['root', '/*'], // root用户，即本应用开发人员，拥有全部权限

                ['user', '概况'], // user用户，即普通用户，拥有基础权限

                ['admin', '概况'],
                ['admin', '应用'],
                ['admin', '员工查看'],
                ['admin', '客户管理'],
//                ['admin', '操作日志'],// admin用户，即普通管理员，其次有一些其他权限

                ['super_admin', '概况'],
                ['super_admin', '应用'],
                ['super_admin', '系统'],// super_admin用户，即高级管理员，其次包含一些用户管理等高级权限
            ]);

        // 4.添加菜单

        $this->batchInsert('{{%menu}}',
            ['id','name','parent','route','order','data'],
            [
                [1, 'L0-Home', NULL, '/by/dashboard/index', 1, '{"text":"概况","group":"true"}'],
                [2, 'L0-Application', NULL, '/by/dashboard/index', 2, '{"text":"应用","group":"true"}'],
                [3, 'L0-System', NULL, '/by/dashboard/index', 3, '{"text":"系统","group":"true"}'],
                [4, 'L1-Dashboard', 1, '/by/dashboard/index', 1, '{"text":"首页","icon":"icon-speedometer","link":"/dashboard/index"}'],
                [5, 'L1-Customer', 3, '/by/dashboard/index', 1, '{"icon":"icon-user","text":"客户管理"}'],
                [6, 'L1-Setting', 3, '/by/dashboard/index', 2, '{"icon":"anticon anticon-setting","text":"设置管理"}'],
                [7, 'L2-CustMgr', 5, '/by/customers/index', 1, '{"link":"/customer/mgr","text":"客户管理"}'],
                [8, 'L2-UserMgr', 6, '/by/users/index', 1, '{"link":"/setting/user","text":"员工管理"}'],
                [9, 'L2-LogMgr', 6, '/by/logs/index', 2,'{"link":"/setting/log","text":"错误日志"}'],
                [10, 'L2-WechatMgr', 6, '/by/wecaht/index', 2,'{"link":"/setting/setting/wechat","text":"微信设置"}'],
            ]);

        // 5.快捷菜单信息
        $quick_start_menu = [];
        array_push($quick_start_menu,
            [
                'avatar' => 'https://gw.alipayobjects.com/zos/rmsportal/WdGqmHpayyMjiEhcKoVE.png',
                'title' => '客户管理',
                'desc' => '查看微信端客户信息',
                'route' => '/customer/mgr'
            ]);
        array_push($quick_start_menu,
            [
                'avatar' => 'https://gw.alipayobjects.com/zos/rmsportal/WdGqmHpayyMjiEhcKoVE.png',
                'title' => '错误日志',
                'desc' => '查看系统错误日志，仅系统开发人员可见',
                'route' => '/setting/log'
            ]);
        array_push($quick_start_menu,
            [
                'avatar' => 'https://gw.alipayobjects.com/zos/rmsportal/WdGqmHpayyMjiEhcKoVE.png',
                'title' => '员工管理',
                'desc' => '管理店铺员工，设置相关权限等',
                'route' => '/setting/user'
            ]);
        array_push($quick_start_menu,
            [
                'avatar' => 'https://gw.alipayobjects.com/zos/rmsportal/WdGqmHpayyMjiEhcKoVE.png',
                'title' => '微信设置',
                'desc' => '设置相关参数，包括店铺设置，微信设置，体验者设置等',
                'route' => '/setting/setting/wechat'
            ]);
        $this->batchInsert('{{%config}}',
            ['symbol','content','encode','sid','created_at','updated_at'],
            [
                ['by_quick_start_menu', serialize($quick_start_menu), 2, 0, time(), time()],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%auth_item}}');
        $this->delete('{{%auth_item_child}}');
        $this->delete('{{%menu}}');
        $this->delete('{{%config}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180724_070952_create_experiencer cannot be reverted.\n";

        return false;
    }
    */
}