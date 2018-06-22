<?php

use yii\db\Migration;
use yii\db\Schema;
/**
 * Class m180612_054805_create_actionlog
 */
class m180612_054805_create_actionlog extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB  COMMENT="操作日志"';
        }
        $this->createTable('{{%actionlog}}', [
            'id'           =>$this->primaryKey()->unsigned()->comment('主键'),
            'user_id'     => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('用户id'),
            'user_remote'=> $this->text()->notNull()->comment('值'),
            'time'        => $this->timestamp()->notNull()->comment('时间'),
            'action'     => $this->string(255)->notNull()->comment('方法'),
            'category'   => $this->string(255)->notNull()->comment('控制器'),
            'status'     => $this->string(255)->Null()->defaultValue(NULL)->comment('状态'),
            'message'    => $this->text()->Null()->defaultValue(NULL)->comment('操作内容'),
            'detail'     => $this->text()->Null()->defaultValue(NULL)->comment('详情'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180612_054805_create_actionlog cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180612_054805_create_actionlog cannot be reverted.\n";

        return false;
    }
    */
}
