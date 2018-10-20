<?php

use yii\db\Migration;

/**
 * Class m181020_024109_change_desc_auth
 */
class m181020_024109_change_desc_auth extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('{{%auth_item}}',['description'=>'除微信高级设置，权限、员工管理等以外的全部权限。(建议将该权限分配给店铺的高管人员)'],['name'=>'admin']);
        $this->update('{{%auth_item}}',['description'=>'几乎全部权限。(强烈建议仅店铺创始人拥有该权限，以确保微信支付设置，员工管理等核心功能的安全性)'],['name'=>'super_admin']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181020_024109_change_desc_auth cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181020_024109_change_desc_auth cannot be reverted.\n";

        return false;
    }
    */
}
