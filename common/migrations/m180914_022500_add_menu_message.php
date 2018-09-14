<?php

use yii\db\Migration;

/**
 * Class m180914_022500_add_menu_message
 */
class m180914_022500_add_menu_message extends Migration
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
                ['/by/customers/welcome', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/customers/upload-temp-media', 0, 2, NULL, NULL, NULL, time(), time()],

            ]);
        // 2 设计权限点
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['客户管理', '/by/customers/welcome'],
                ['客户管理', '/by/customers/upload-temp-media'],
            ]);

        // 3 添加菜单
        $this->update('{{%menu}}',['data'=>'{"link":"/customer/mgr","text":"客户列表"}'],['id'=>7]);
        $this->batchInsert('{{%menu}}',
            ['id','name','parent','route','order','data'],
            [
                [16, 'L2-MessageMgr', 5, '/by/customers/welcome', 1, '{"link":"/customer/message","text":"客服消息"}'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->delete('{{%auth_item}}',['name'=>"/by/customers/welcome"]);
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"客户管理"],['child'=>"/by/customers/welcome"]]);

        $this->delete('{{%auth_item}}',['name'=>"/by/customers/upload-temp-media"]);
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"客户管理"],['child'=>"/by/customers/upload-temp-media"]]);

        $this->delete('{{%menu}}',['id'=>16]);

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180914_022500_add_menu_message cannot be reverted.\n";

        return false;
    }
    */
}
