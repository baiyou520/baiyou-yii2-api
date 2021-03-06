<?php
use yii\db\Schema;
use yii\db\Migration;

class m180610_093725_new_instance extends Migration
{

    public function safeUp(){

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB COMMENT '实例表'";
        }

        $this->createTable('{{%instance}}',[
            'sid'               => $this->integer()->unsigned()->comment('sid，来自总后台数据库instance表中instance_id'),
            'user_id'           => $this->integer()->unsigned()->notNull()->comment('user_id，来自总后台数据库user表中的id'),
            'name'              => $this->string(50)->notNull()->defaultValue('')->comment('实例名称，如：百优甄选'),
            'thumb'             => $this->string(255)->notNull()->defaultValue('')->comment('实例头像，取值微信小程序图标'),
            'certificate_flag'  => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0)->comment('是否认证，0：未认证，1：已认证'),
            'level'             => $this->string(20)->notNull()->defaultValue('')->comment('实例级别，如：初级版'),
            'is_bind'           => $this->tinyInteger()->notNull()->defaultValue(0)->comment('是否绑定，0：未绑定，1：绑定'),
            'expired_at'        => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('到期时间'),
            'applet_appid'      => $this->string(18)->defaultValue('')->comment('微信小程序id'),
            'applet_appsecret'  => $this->string(32)->defaultValue('')->comment('微信小程序密钥'),
            'wx_mch_id'         => $this->string(10)->defaultValue('')->comment('微信支付分配的商户号'),
            'wx_mch_key'        => $this->string(32)->defaultValue('')->comment('商户平台设置的密钥key'),
            'ssl_cert_path'     => $this->string(40)->defaultValue('')->comment('cert证书地址'),
            'ssl_key_path'      => $this->string(40)->defaultValue('')->comment('key证书地址'),
            'experience_qrcode' => $this->string(100)->defaultValue('')->comment('体验版二维码'),
            'online_qrcode'     => $this->string(100)->defaultValue('')->comment('上线小程序码'),
            'status'            => $this->tinyInteger()->notNull()->defaultValue(0)->comment('0:试用，1:正常，-1:过期，-2:删除'),
            'created_at'        => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('时间戳，创建时间'),
            'updated_at'        => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('时间戳，修改时间'),
            'PRIMARY KEY ([[sid]])',
        ],$tableOptions);

        // 插入sid 为0的实例，仅为了实现外键，因为有些表中含有sid为0的数据
        $this->insert('{{%instance}}', [
            'sid' => 0,
            'user_id' => 1
        ]);
    }

    public function safeDown(){
        $this->dropTable('{{%instance}}');
    }
}
