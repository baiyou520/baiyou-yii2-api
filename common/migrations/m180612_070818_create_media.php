<?php

use yii\db\Migration;

/**
 * Class m180627_110818_create_media
 */
class m180612_070818_create_media extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB COMMENT="媒体资源表"';
        }
        $this->createTable('{{%media}}', [
            'media_id'       => $this->primaryKey(11)->unsigned()->comment('主键'),
            'name'           => $this->string(100)->notNull()->comment('资源名称'),
            'url'            => $this->string(100)->notNull()->comment('地址'),
            'type'           => $this->tinyInteger()->unsigned()->notNull()->comment('类型：1.图片2.音频3.视频'),
            'group_id'       => $this->integer()->unsigned()->notNull()->comment('分组id,CG表S=pic_group/S=audio_group/S=video_group'),
            'sid'            => $this->integer()->unsigned()->notNull()->comment('sid，来自总后台数据库instance表中instance_id'),
            'key'            => $this->string(512)->notNull()->defaultValue('')->comment('文件对应到云存储服务中的唯一标识,未来扩展七牛云备用'),
            'description'    => $this->string(1024)->notNull()->defaultValue('')->comment('文件的描述内容'),
            'height'         => $this->smallInteger()->unsigned()->notNull()->comment('高度'),
            'width'          => $this->smallInteger()->unsigned()->notNull()->comment('宽度'),
            'status'         => $this->tinyInteger()->notNull()->defaultValue(1)->comment('状态:0,软删除，1，正常'),
            'created_at'     => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at'     => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳'),
            'FOREIGN KEY ([[sid]]) REFERENCES instance ([[sid]]) ON DELETE NO ACTION ON UPDATE NO ACTION',
            'FOREIGN KEY ([[group_id]]) REFERENCES category ([[category_id]]) ON DELETE NO ACTION ON UPDATE NO ACTION',
        ], $tableOptions);
    }
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('media');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180627_110818_create_media cannot be reverted.\n";

        return false;
    }
    */
}
