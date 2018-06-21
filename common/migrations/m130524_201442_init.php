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
            'id'                => $this->primaryKey()->unsigned()->comment('id，主键自增'),
            'username'          => $this->string(100)->notNull()->comment('用户名(即登录名)	'),
            'name'              => $this->string(50)->notNull()->comment('姓名(昵称)'),
            'avatar_thumb'      => $this->string(100)->notNull()->defaultValue(' ')->comment('小头像(缩略图80*80)	'),
            'avatar'            => $this->string(100)->notNull()->defaultValue(' ')->comment('大头像(原图)'),
            'auth_key'           => $this->string(32)->notNull()->comment('yii2认证key'),
            'password_hash'     => $this->string(255)->notNull()->comment('密码'),
            'password_reset_token' => $this->string(255)->Null()->defaultValue(NULL)->comment('密码token'),
            'access_token_expired_at' => $this->timestamp()->Null()->defaultValue(NULL)->comment('JWT认证(用于api)	过期时间'),
            'email'              => $this->string(50)->Null()->defaultValue(' ')->comment('邮箱'),
            'phone'             => $this->string(20)->Null()->defaultValue(' ')->comment('电话'),
            'status'            => $this->tinyInteger()->unsigned()->notNull()->defaultValue(10)->comment('激活状态:10为启用，0位禁用'),
            'last_login_at'     => $this->timestamp()->Null()->defaultValue(NULL)->comment('最后登录时间	'),
            'last_login_ip'     => $this->string(15)->Null()->defaultValue(' ')->comment('最后登录ip'),
            'created_at'        => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at'         => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳'),
        ], $tableOptions);
        $this->insert('{{%user}}', [
            'username' => 'sadmin',
            'name' => '超级管理员',
            'auth_key' => 'ynHBGd5ndD032AC76oDCBck9VGVlVTsp',
            'password_hash' => '$2y$13$zXfbGfH7ez8xHDfsx5CDMODffHt47Q/mHFezmKT7/yvGauuxEqSwy'
        ]);
        $this->insert('{{%user}}', [
            'username' => 'admin',
            'name' => '管理员',
            'auth_key' => 'ynHBGd5ndD032AC76oDCBck9VGVlVTsp',
            'password_hash' => '$2y$13$zXfbGfH7ez8xHDfsx5CDMODffHt47Q/mHFezmKT7/yvGauuxEqSwy'
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
