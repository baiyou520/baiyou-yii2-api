<?php

use yii\db\Migration;
use yii\db\Schema;
/**
 * Class m180612_030312_create_notice
 */
class m180612_030312_create_notice extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB  COMMENT="通知已读标记表"';
        }

        $this->createTable('{{%notice_user}}', [
            'notice_user_id'       =>$this->primaryKey()->unsigned()->comment('主键'),
            'sid'             => $this->integer()->unsigned()->notNull()->comment('sid，来自总后台数据库instance表中instance_id'),
            'notice_id'         => $this->integer()->unsigned()->notNull()->comment('通知id'),
            'user_id'           => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('用户id'),
            'is_read'          => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0)->comment('是否已读，1:已读，0:未读'),
            'created_at'      => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at'      => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳'),
            'FOREIGN KEY ([[sid]]) REFERENCES instance ([[sid]]) ON DELETE NO ACTION ON UPDATE NO ACTION',
            'FOREIGN KEY ([[notice_id]]) REFERENCES notice ([[notice_id]]) ON DELETE NO ACTION ON UPDATE NO ACTION',
            'FOREIGN KEY ([[user_id]]) REFERENCES user ([[id]]) ON DELETE NO ACTION ON UPDATE NO ACTION'
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%notice}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180612_030312_create_notice cannot be reverted.\n";

        return false;
    }
    */
}
