<?php

use yii\db\Migration;

/**
 * Class m181009_105233_add_premission_upgrade
 */
class m181009_105233_add_premission_upgrade extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1.添加路由
        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['/by/configs/submit-upgrade', 0, 2, NULL, NULL, NULL, time(), time()],
            ]);
        // 2 设计权限点
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['微信设置', '/by/configs/submit-upgrade'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181009_105233_add_premission_upgrade cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181009_105233_add_premission_upgrade cannot be reverted.\n";

        return false;
    }
    */
}
