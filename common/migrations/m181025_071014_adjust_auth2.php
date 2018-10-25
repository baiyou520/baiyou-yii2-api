<?php

use yii\db\Migration;

/**
 * Class m181025_071014_adjust_auth2
 */
class m181025_071014_adjust_auth2 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['客服消息', '/by/customers/upload-temp-media'],
                ['客服消息', '/by/customers/welcome']
            ]);

        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['客户管理', 0, 2, 'L2整个微信端客户管理权限', NULL, NULL, time(), time()]
            ]);
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['admin', '客户管理'],
                ['微信管理', '客户管理'],
                ['客户管理', '客户修改'],
                ['客户管理', '客户查看']
            ]);
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"微信管理"],['child'=>"/by/customers/welcome"]]);
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"微信管理"],['child'=>"/by/customers/upload-temp-media"]]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181025_071014_adjust_auth2 cannot be reverted.\n";

        return false;
    }
    */
}
