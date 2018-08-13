<?php

use yii\db\Migration;
use yii\db\Schema;
/**
 * Class m180612_022844_create_customer
 */
class m180612_022844_create_customer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB  COMMENT="客户表(C端用户表)"';
        }

        $this->createTable('{{%customer}}', [
            'id'                        =>$this->primaryKey(11)->unsigned()->comment('主键'),
            'sid'                       => $this->integer()->unsigned()->notNull()->comment('sid，来自总后台数据库instance表中instance_id'),
            'username'                  => $this->string(100)->notNull()->comment('用户名(即登录名)'),
            'avatar'                    => $this->string(255)->notNull()->defaultValue('')->comment('大头像(原图)'),
            'nickname'                  => $this->string(50)->notNull()->comment('微信昵称'),
            'name'                      => $this->string(50)->notNull()->comment('真实姓名(比如取自订单地址)'),
            'openid'                    => $this->string(28)->notNull()->comment('微信移动端标识符'),
            'access_token_expired_at'   => $this->timestamp()->notNull()->defaultValue(NULL)->comment('JWT认证(用于api)	过期时间'),
            'phone'                     => $this->string(20)->notNull()->defaultValue('')->comment('电话'),
            'parent_id'                 => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('推荐人id'),
            'source_from'               => $this->string(100)->notNull()->defaultValue('')->comment('注册来源'),
            'status'                    => $this->tinyInteger()->unsigned()->notNull()->defaultValue(10)->comment('激活状态:10为启用，0位禁用'),
            'last_login_at'             => $this->timestamp()->notNull()->defaultValue(NULL)->comment('最后登录时间'),
            'last_login_ip'             => $this->string(15)->notNull()->defaultValue('')->comment('最后登录ip'),
            'created_at'                => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at'                => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳'),
            'FOREIGN KEY ([[sid]]) REFERENCES instance ([[sid]]) ON DELETE NO ACTION ON UPDATE NO ACTION'
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%customer}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180612_022844_create_customer cannot be reverted.\n";

        return false;
    }
    */
}
