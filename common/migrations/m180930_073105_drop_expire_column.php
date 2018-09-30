<?php

use yii\db\Migration;

/**
 * Class m180930_073105_drop_expire_column
 */
class m180930_073105_drop_expire_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('customer', 'access_token_expired_at');
        $this->alterColumn('config', 'symbol', 'VARCHAR(50) NOT NULL COMMENT "标识"');
        $this->alterColumn('category', 'symbol', 'VARCHAR(50) NOT NULL COMMENT "标识"');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180930_073105_drop_expire_column cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180930_073105_drop_expire_column cannot be reverted.\n";

        return false;
    }
    */
}
