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
            'id'                        =>Schema::TYPE_INTEGER.'(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL COMMENT "主键"',
            'username'                  => $this->string(100)->notNull()->comment('用户名(即登录名)	'),
            'avatar'                    => $this->string(255)->notNull()->defaultValue(' ')->comment('大头像(原图)'),
            'nickname'                  => $this->string(50)->notNull()->comment('微信昵称'),
            'name'                      => $this->string(50)->notNull()->comment('真实姓名(比如取自订单地址)'),
            'openid'                    => $this->string(28)->notNull()->comment('微信移动端标识符'),
            'access_token_expired_at' => $this->timestamp()->notNull()->defaultValue(NULL)->comment('JWT认证(用于api)	过期时间'),
            'phone'           => $this->string(20)->notNull()->defaultValue(' ')->comment('电话'),
            'parent_id'      => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('推荐人id'),
            'status'         => $this->tinyInteger(2)->unsigned()->notNull()->defaultValue(10)->comment('激活状态:10为启用，0位禁用'),
            'last_login_at' => $this->timestamp()->notNull()->defaultValue(NULL)->comment('最后登录时间	'),
            'last_login_ip' => $this->string(14)->notNull()->defaultValue(' ')->comment('最后登录ip'),
            'created_at'    => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at'    => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180612_022844_create_customer cannot be reverted.\n";

        return false;
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
