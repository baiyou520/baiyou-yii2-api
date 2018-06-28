<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB  COMMENT="用户表(员工表)"';
        }

        $this->createTable('{{%user}}', [
            'id'                => $this->integer()->unsigned()->comment('id，来自总后台数据库'),
            'sid'               => $this->integer()->unsigned()->comment('sid，来自总后台数据库instance表中instance_id'),
            'username'          => $this->string(30)->notNull()->comment('用户名(即百优账号)'),
            'name'              => $this->string(30)->notNull()->comment('姓名(昵称)'),
            'phone'             => $this->string(20)->notNull()->comment('联系方式(电话)'),
            'status'            => $this->tinyInteger()->unsigned()->notNull()->defaultValue(10)->comment('激活状态:10为启用，0位禁用'),
            'created_at'        => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at'        => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳'),
            'PRIMARY KEY ([[id]], [[sid]])',
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
