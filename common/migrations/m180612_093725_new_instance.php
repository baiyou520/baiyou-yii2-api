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

        $this->createTable('instance',[
            'instance_id'       => $this->primaryKey()->comment('id自增'),
            'app_id'            => $this->integer()->notNull()->comment('所属应用id'),
            'user_id'           => $this->integer()->notNull()->comment('购买者id'),
            'name'              => $this->string(20)->notNull()->defaultValue('')->comment('实例名称，如：百优甄选'),
            'certificate_flag'  => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('是否认证，0：未认证，1：已认证'),
            'level'             => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('实例等级：0：未认证，1：初级，2：中级'),
            'expired_at'        => $this->integer()->notNull()->comment('到期时间'),
            'applet_appid'      => $this->string(18)->defaultValue('')->comment('微信小程序id'),
            'applet_appsecret'  => $this->string(32)->defaultValue('')->comment('微信小程序密钥'),
            'status'            => $this->tinyInteger(1)->notNull()->defaultValue(1)->comment('0:已关闭，1:正常'),
            'created_at'        => $this->integer()->notNull()->defaultValue(0)->comment('时间戳，创建时间'),
            'updated_at'        => $this->integer()->notNull()->defaultValue(0)->comment('时间戳，修改时间')

        ],$tableOptions);

//        $this->addForeignKey('app_id','instance','app_id','app','app_id','NO ACTION','NO ACTION');
        $this->addForeignKey('user_id','instance','user_id','user','id','NO ACTION','NO ACTION');
    }

    public function safeDown(){
        $this->dropTable('instance');
    }
}
