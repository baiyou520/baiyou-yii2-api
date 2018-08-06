<?php

use yii\db\Migration;

/**
 * Class m180724_070952_create_experiencer
 */
class m180612_070952_create_experiencer extends Migration
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
        $this->createTable('{{%experiencer}}', [
            'experiencer_id'    => $this->primaryKey()->unsigned()->comment('主键'),
            'sid'               => $this->integer()->unsigned()->notNull()->comment('sid，来自总后台数据库instance表中instance_id'),
            'userstr'           => $this->string(100)->notNull()->comment('添加成功后微信端返回的编号'),
            'wechat_id'         => $this->string(100)->notNull()->comment('微信id'),
            'name'              => $this->string(10)->notNull()->defaultValue('')->comment('真实姓名'),
            'status'            => $this->tinyInteger()->unsigned()->notNull()->comment('状态:0.解绑，1.绑定'),
            'created_at'        => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at'        => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳'),
            'FOREIGN KEY ([[sid]]) REFERENCES instance ([[sid]]) ON DELETE NO ACTION ON UPDATE NO ACTION',
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%experiencer}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180724_070952_create_experiencer cannot be reverted.\n";

        return false;
    }
    */
}
