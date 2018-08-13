<?php

use yii\db\Migration;
use yii\db\Schema;
/**
 * Class m180612_032913_create_config
 */
class m180612_032913_create_config extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB  COMMENT="配置表"';
        }

        $this->createTable('{{%config}}', [
            'config_id'     => $this->primaryKey()->unsigned()->comment('主键'),
            'sid'               => $this->integer()->unsigned()->notNull()->comment('sid，来自总后台数据库instance表中instance_id'),
            'symbol'        => $this->string(20)->notNull()->comment('标识'),
            'content'       => $this->text()->notNull()->comment('值'),
            'encode'        => $this->tinyInteger()->unsigned()->notNull()->defaultValue(1)->comment('值的编码形式1:string,2:json,3:int'),
            'created_at'    => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at'    => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳')
        ], $tableOptions);


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%config}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180612_032913_create_config cannot be reverted.\n";

        return false;
    }
    */
}
