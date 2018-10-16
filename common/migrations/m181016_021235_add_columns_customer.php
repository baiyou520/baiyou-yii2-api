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
        $this->addColumn('customer', 'gender', 'TINYINT(3) NOT NULL COMMENT "性别，1：男，2:女" AFTER `nickname`');
        $this->addColumn('customer', 'city', 'VARCHAR(30) NOT NULL COMMENT "城市" AFTER `nickname`');
        $this->addColumn('customer', 'country', 'VARCHAR(30) NOT NULL COMMENT "国家" AFTER `nickname`');
        $this->addColumn('customer', 'province', 'VARCHAR(30) NOT NULL COMMENT "省份" AFTER `nickname`');
        $this->addColumn('customer', 'language', 'VARCHAR(20) NOT NULL COMMENT "语言" AFTER `nickname`');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181016_021235_add_columns_customer cannot be reverted.\n";

        return false;
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
