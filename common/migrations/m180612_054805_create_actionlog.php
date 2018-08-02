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
        $this->createTable('{{%action_log}}', [
            'action_log_id'     => $this->primaryKey()->unsigned()->comment('主键'),
            'sid'               => $this->integer()->unsigned()->notNull()->comment('sid，来自总后台数据库instance表中instance_id'),
            'user_id'           => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('用户id'),
            'user_ip'           => $this->string(15)->notNull()->defaultValue('')->comment('IP'),
            'trigger_from'      => $this->tinyInteger()->notNull()->defaultValue(0)->comment('日志来源:0,中台，1，微信'),
            'action'            => $this->string(100)->notNull()->defaultValue('')->comment('方法'),
            'controller'        => $this->string(100)->notNull()->defaultValue('')->comment('控制器'),
            'module'            => $this->string(20)->notNull()->defaultValue('')->comment('操作模块'),
            'status'            => $this->tinyInteger()->notNull()->defaultValue(1)->comment('状态:0,给开发人员看，1，给客户看'),
            'message'           => $this->text()->Null()->defaultValue(NULL)->comment('操作内容'),
            'detail'            => $this->text()->Null()->defaultValue(NULL)->comment('详情'),
            'created_at'        => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at'        => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳'),
//            'FOREIGN KEY ([[user_id]]) REFERENCES user ([[id]]) ON DELETE NO ACTION ON UPDATE NO ACTION' ,
            'FOREIGN KEY ([[sid]]) REFERENCES instance ([[sid]]) ON DELETE NO ACTION ON UPDATE NO ACTION' ,
        ], $tableOptions);

//        // 创建view ，方便查询，效果可看中台操作日志页面.  注意由于是多租户设计要添加最后的where条件，来区别sid
        // 2018-8-2,已经用fields解决
//        $this->execute("create view action_log_view as select `action_log`.`message` AS `message`,`action_log`.`created_at` AS `created_at`,
//            `user`.`name` AS `name`,`action_log`.`sid` AS `sid`,`action_log`.`status` AS `status`,`action_log`.`module` AS `module` from
//            (`action_log` join `user`)  where (`action_log`.`sid` = `user`.`sid` and `action_log`.`user_id` = `user`.`id`)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%actionlog}}');
//        $this->execute("drop view if exists action_log_view");
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
