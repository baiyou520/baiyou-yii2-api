<?php

use yii\db\Migration;

/**
 * Class m180912_105215_drop_fk_notice_user
 */
class m180912_105215_drop_fk_notice_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('notice_user_ibfk_3','notice_user');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addForeignKey('notice_user_ibfk_3','notice_user',
            'user_id','user','id','NO ACTION','NO ACTION');
    }
//ALTER TABLE `notice_user` ADD CONSTRAINT `notice_user_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180912_105215_drop_fk_notice_user cannot be reverted.\n";

        return false;
    }
    */
}
