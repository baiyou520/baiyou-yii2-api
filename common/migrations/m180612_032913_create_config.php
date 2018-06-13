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
            'config_id'     =>Schema::TYPE_INTEGER.'(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL COMMENT "主键"',
            'symbol'        => $this->string(20)->notNull()->comment('标识'),
            'content'       => $this->text()->notNull()->comment('值'),
            'encode'        => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1)->comment('值的编码形式1:string,2:json,3:int'),
            'created_at'    => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at'    => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180612_032913_create_config cannot be reverted.\n";

        return false;
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
