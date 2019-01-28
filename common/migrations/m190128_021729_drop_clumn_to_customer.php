<?php

use yii\db\Migration;

/**
 * Class m190128_021729_drop_clumn_to_customer
 */
class m190128_021729_drop_clumn_to_customer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('customer_ibfk_1','customer');

        $this->dropColumn('customer','sid');
        $this->dropColumn('customer','openid');
        $this->dropColumn('customer','parent_id');
        $this->dropColumn('customer','source_from');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('customer','sid','INT(11) UNSIGNED NOT NULL COMMENT "sid，来自总后台数据库instance表中instance_id" AFTER `id`');

        $this->addForeignKey('customer_ibfk_1','customer','sid','instance','sid');
        $this->addColumn('customer','openid','VARCHAR(28) NOT NULL DEFAULT "" COMMENT "微信移动端标识符" AFTER `name`');
        $this->addColumn('customer','parent_id','INT(11) UNSIGNED NOT NULL DEFAULT "0" COMMENT "推荐人id" AFTER `phone`');
        $this->addColumn('customer','source_from','VARCHAR(100) NOT NULL DEFAULT "" COMMENT "注册来源" AFTER `parent_id`');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190128_021729_drop_clumn_to_customer cannot be reverted.\n";

        return false;
    }
    */
}
