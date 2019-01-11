<?php

use yii\db\Migration;

/**
 * Class m190111_025356_add_premission_homealias
 */
class m190111_025356_add_premission_homealias extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['/by/dashboard/get-home-alias', 0, 2, NULL, NULL, NULL, time(), time()],
            ]);

        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['首页', '/by/dashboard/get-home-alias'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190111_025356_add_premission_homealias cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190111_025356_add_premission_homealias cannot be reverted.\n";

        return false;
    }
    */
}
