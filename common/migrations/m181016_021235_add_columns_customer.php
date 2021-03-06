<?php

use yii\db\Migration;

/**
 * Class m181016_021235_add_columns_customer
 */
class m181016_021235_add_columns_customer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('customer', 'gender', 'TINYINT(3) NOT NULL DEFAULT 0 COMMENT "性别，1：男，2:女,0:未知" AFTER `nickname`');
        $this->addColumn('customer', 'city', 'VARCHAR(30) NOT NULL DEFAULT "" COMMENT "城市" AFTER `nickname`');
        $this->addColumn('customer', 'country', 'VARCHAR(30) NOT NULL DEFAULT "" COMMENT "国家" AFTER `nickname`');
        $this->addColumn('customer', 'province', 'VARCHAR(30) NOT NULL DEFAULT "" COMMENT "省份" AFTER `nickname`');
        $this->addColumn('customer', 'language', 'VARCHAR(20) NOT NULL DEFAULT "" COMMENT "语言" AFTER `nickname`');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('customer','gender');
        $this->dropColumn('customer','city');
        $this->dropColumn('customer','country');
        $this->dropColumn('customer','province');
        $this->dropColumn('customer','language');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181016_021235_add_columns_customer cannot be reverted.\n";

        return false;
    }
    */
}
