<?php

use yii\db\Migration;

/**
 * Class m180613_015200_create_demo
 */
class m180613_015200_create_demo extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB COMMENT 'Demo示例表'";
        }

        $this->createTable('{{%demo}}',[
            'demo_id'       => $this->primaryKey()->unsigned()->comment('主键'),
            'name'              => $this->string(30)->notNull()->comment('名称'),
            'avatar'            => $this->string(200)->notNull()->defaultValue('https://img.zcool.cn/community/01786557e4a6fa0000018c1bf080ca.png@2o.png')->comment('头像地址'),
            'call_no'           => $this->integer()->notNull()->comment('调用次数'),
            'status'            => $this->tinyInteger(1)->notNull()->defaultValue(1)->comment('0:已关闭，1:正常'),
            'created_at'        => $this->integer()->notNull()->defaultValue(0)->comment('时间戳，创建时间'),
            'updated_at'        => $this->integer()->notNull()->defaultValue(0)->comment('时间戳，修改时间')

        ],$tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%demo}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180613_015200_create_demo cannot be reverted.\n";

        return false;
    }
    */
}
