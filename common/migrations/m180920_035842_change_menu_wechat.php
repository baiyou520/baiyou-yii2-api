<?php

use yii\db\Migration;

/**
 * Class m180920_035842_change_menu_wechat
 */
class m180920_035842_change_menu_wechat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('{{%menu}}',['data'=>'{"link":"/customer/setting","text":"微信设置"}'],['id'=>10]);
        $this->addColumn('demo', 'form_id', 'VARCHAR(32) NOT NULL DEFAULT "" COMMENT "微信表单id" AFTER `call_no`');
        $this->addColumn('demo', 'desc', 'TEXT NULL COMMENT "描述" AFTER `call_no`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180920_035842_change_menu_wechat cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180920_035842_change_menu_wechat cannot be reverted.\n";

        return false;
    }
    */
}
