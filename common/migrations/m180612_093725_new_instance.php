<?php
use yii\db\Schema;
use yii\db\Migration;

class m180612_093725_new_instance extends Migration
{

    public function safeUp(){

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB COMMENT '实例表'";
        }

        $this->createTable('{{%instance}}',[
            'sid'               => $this->integer()->unsigned()->comment('sid，来自总后台数据库instance表中instance_id'),
            'user_id'           => $this->integer()->unsigned()->notNull()->comment('user_id，来自总后台数据库user表中的id'),
            'name'              => $this->string(20)->notNull()->defaultValue('')->comment('实例名称，如：百优甄选'),
            'certificate_flag'  => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0)->comment('是否认证，0：未认证，1：已认证'),
            'level'             => $this->string(20)->notNull()->defaultValue('未认证')->comment('实例级别，如：初级版'),
            'expired_at'        => $this->integer()->unsigned()->notNull()->comment('到期时间'),
            'applet_appid'      => $this->string(18)->defaultValue('')->comment('微信小程序id'),
            'applet_appsecret'  => $this->string(32)->defaultValue('')->comment('微信小程序密钥'),
            'status'            => $this->tinyInteger()->notNull()->defaultValue(1)->comment('0:已关闭，1:正常'),
            'created_at'        => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('时间戳，创建时间'),
            'updated_at'        => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('时间戳，修改时间'),
            'PRIMARY KEY ([[sid]])',
        ],$tableOptions);
    }

    public function safeDown(){
        $this->dropTable('{{%instance}}');
    }
}
