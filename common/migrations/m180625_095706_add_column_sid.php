<?php

use yii\db\Migration;

/**
 * Class m180625_095706_add_column_sid
 */
class m180625_095706_add_column_sid extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('actionlog','sid','integer not null');
        $this->addColumn('category','sid','integer not null');
        $this->addColumn('config','sid','integer not null');
        $this->addColumn('customer','sid','integer not null');
        $this->addColumn('demo','sid','integer not null');
//        $this->addColumn('log','sid','integer not null');
        $this->addColumn('notice','sid','integer not null');
//        $this->addColumn('auth_assignment','sid','integer not null');
//        $this->addPrimaryKey('sid','auth_assignment','sid');
//        $this->addColumn('user','sid','integer not null');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180625_095706_add_column_sid cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180625_095706_add_column_sid cannot be reverted.\n";

        return false;
    }
    */
}
