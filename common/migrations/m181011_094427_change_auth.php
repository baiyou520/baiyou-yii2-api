<?php

use yii\db\Migration;

/**
 * Class m181011_094427_change_auth
 */
class m181011_094427_change_auth extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1.添加路由
        $this->update('{{%auth_item}}',['description'=>'除微信高级设置，权限、员工管理等以外的全部权限'],['name'=>'admin']);
        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['微信高级设置', 0, 2, 'L2设置微信小程序支付等相关信息', NULL, NULL, time(), time()],
            ]);
        // 2 设计权限点
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"微信设置"],['child'=>"/by/configs/set-applet-secret"]]);
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"微信设置"],['child'=>"/by/configs/upload-cert"]]);
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"微信设置"],['child'=>"/by/configs/set-notice-phone"]]);
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['admin', '我的文件'],
                ['admin', '门店信息'],
                ['admin', '操作日志'],
                ['admin', '微信设置'],
                ['微信高级设置', '/by/configs/set-applet-secret'],
                ['微信高级设置', '/by/configs/upload-cert'],
                ['微信高级设置', '/by/configs/set-notice-phone'],
                ['设置', '微信高级设置'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181011_094427_change_auth cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181011_094427_change_auth cannot be reverted.\n";

        return false;
    }
    */
}
