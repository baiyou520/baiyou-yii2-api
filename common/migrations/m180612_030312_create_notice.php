<?php

use yii\db\Migration;
use yii\db\Schema;
/**
 * Class m180612_030312_create_notice
 */
class m180612_030312_create_notice extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB  COMMENT="通知表"';
        }

        $this->createTable('{{%notice}}', [
            'notice_id'      =>$this->primaryKey()->unsigned()->comment('主键'),
            'user_id'         => $this->integer()->unsigned()->notNull()->comment('用户id'),
            'title'           => $this->string(50)->notNull()->defaultValue(' ')->comment('标题'),
            'content'         => $this->text()->notNull()->comment('内容'),
            'type'            => $this->tinyInteger()->unsigned()->notNull()->defaultValue(1)->comment(' 类型：1:通知,2:消息,3:待办'),
            'related_route'  => $this->string(100)->notNull()->defaultValue(' ')->comment('跳转用的相关路由'),
            'related_id'     => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('跳转到的对应ID'),
            'tips'            => $this->string(20)->notNull()->defaultValue(' ')->comment(' 额外提示'),
            'tips_level'     => $this->tinyInteger()->unsigned()->notNull()->defaultValue(1)->comment(' 额外提示程度：1:todo,2:urgent,3:doing,4:processing'),
            'status'         => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0)->comment('激活状态:10为启用，0位禁用'),
            'created_at'    => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at'    => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180612_030312_create_notice cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180612_030312_create_notice cannot be reverted.\n";

        return false;
    }
    */
}
