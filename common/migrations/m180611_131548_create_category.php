<?php

use yii\db\Migration;

/**
 * Class m180611_131548_create_category
 */
class m180611_131548_create_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB COMMENT="类别表"';
        }

        $this->createTable('{{%category}}', [
            'category_id' => $this->primaryKey()->unsigned()->comment('主键'),
            'sid'               => $this->integer()->unsigned()->notNull()->comment('sid，来自总后台数据库instance表中instance_id'),
            'category_pid'=> $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('父级id'),
            'symbol'      => $this->string(20)->notNull()->comment('标识'),
            'thumb'       => $this->string(100)->notNull()->defaultValue('')->comment('分类图标'),
            'name'        => $this->string(20)->notNull()->comment('名称'),
            'sort'        => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0)->comment('排序'),
            'data'        => $this->text()->comment('其他配置项JSON编码'),
            'created_at'  => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at'  => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳')
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%category}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180611_131548_create_category cannot be reverted.\n";

        return false;
    }
    */
}
