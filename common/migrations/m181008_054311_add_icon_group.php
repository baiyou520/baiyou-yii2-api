<?php

use yii\db\Migration;

/**
 * Class m181008_054311_add_icon_group
 */
class m181008_054311_add_icon_group extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('media', 'type', 'TINYINT(3) NOT NULL COMMENT "类型：1.图片2.音频3.视频,4.图标"');
        $this->dropForeignKey('media_ibfk_2','media');
        $this->alterColumn('media', 'group_id', 'INT(11) UNSIGNED NOT NULL COMMENT "分组id"');
        $this->addForeignKey('media_ibfk_2','media','group_id','category',
            'category_id','NO ACTION','NO ACTION');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181008_054311_add_icon_group cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181008_054311_add_icon_group cannot be reverted.\n";

        return false;
    }
    */
}
