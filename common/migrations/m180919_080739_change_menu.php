<?php

use yii\db\Migration;

/**
 * Class m180919_080739_change_menu
 */
class m180919_080739_change_menu extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1.添加路由
        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['/by/configs/activate-tpl', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/configs/get-tpl-keywords-ids', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/configs/msg-tpl', 0, 2, NULL, NULL, NULL, time(), time()], // 辅助路由，没有实际action，用于生成菜单
                ['/by/media/my-file', 0, 2, NULL, NULL, NULL, time(), time()], // 辅助路由，没有实际action，用于生成菜单
                ['/by/authorities/auth-mgr', 0, 2, NULL, NULL, NULL, time(), time()], // 辅助路由，没有实际action，用于生成菜单
                ['模板消息', 0, 2, NULL, NULL, NULL, time(), time()],
            ]);

        // 2 设计权限点
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['客户管理', '模板消息'],
                ['模板消息', '/by/configs/index'],
                ['模板消息', '/by/configs/create'],
                ['模板消息', '/by/configs/msg-tpl'],
                ['模板消息', '/by/configs/activate-tpl'],
                ['模板消息', '/by/configs/get-tpl-keywords-ids'],
                ['我的文件', '/by/media/my-file'],
                ['角色查看', '/by/authorities/auth-mgr'],
            ]);

        // 3 修改菜单
        $this->update('{{%menu}}',['data'=>'{"icon":"icon-user","text":"微信管理"}'],['id'=>5]);
        $this->update('{{%menu}}',['route'=>'/by/media/my-file'],['id'=>15]);
        $this->update('{{%menu}}',['route'=>'/by/authorities/auth-mgr'],['id'=>14]);
        $this->update('{{%menu}}',['data'=>'{"link":"/customer/setting/wechat","text":"微信设置"}','parent' =>5],['id'=>10]);
        $this->batchInsert('{{%menu}}',
            ['id','name','parent','route','order','data'],
            [
                [18, 'L2-MsgtplMgr', 5, '/by/configs/msg-tpl', 1, '{"link":"/customer/template","text":"模板消息"}'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180919_080739_change_menu cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180919_080739_change_menu cannot be reverted.\n";

        return false;
    }
    */
}
