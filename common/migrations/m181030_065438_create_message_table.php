<?php

use yii\db\Migration;

/**
 * Handles the creation of table `message`.
 */
class m181030_065438_create_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB  COMMENT="回复消息表"';
        }
        $this->createTable('{{%message}}', [
            'message_id'                    => $this->primaryKey(11)->unsigned()->comment('消息id'),
            'type'                  => $this->tinyInteger()->unsigned()->notNull()->comment('1 个人回复  2 公共回复'),
            'user_id'   => $this->integer()->unsigned()->defaultValue(0)->comment('员工id'),
            'title'          => $this->string(100)->notNull()->comment('标题'),
            'content'           => $this->text()->Null()->defaultValue(NULL)->comment('内容'),
            'sort'           => $this->tinyInteger()->unsigned()->notNull()->comment('排序'),
            'sid'            => $this->integer()->unsigned()->notNull()->comment('sid，来自总后台数据库instance表中instance_id'),
            'created_at'     => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at'     => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳'),
            'FOREIGN KEY ([[sid]]) REFERENCES instance ([[sid]]) ON DELETE NO ACTION ON UPDATE NO ACTION'
        ], $tableOptions);
        // 1.添加路由
        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['/by/messages/index', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/messages/update', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/messages/delete', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/messages/create', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/messages/view', 0, 2, NULL, NULL, NULL, time(), time()]
            ]);
        // 2 设计权限点
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['客服消息', '/by/messages/index'],
                ['客服消息', '/by/messages/update'],
                ['客服消息', '/by/messages/delete'],
                ['客服消息', '/by/messages/create'],
                ['客服消息', '/by/messages/view']
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('message');
    }
}
