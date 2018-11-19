<?php

use yii\db\Migration;

/**
 * Class m181119_080721_add_openid_to_user
 */
class m181119_080721_add_openid_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'openid', 'VARCHAR(28) NOT NULL DEFAULT "" COMMENT "微信移动端标识符" AFTER `phone`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user','openid');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181119_080721_add_openid_to_user cannot be reverted.\n";

        return false;
    }
    */
}
