<?php

use yii\db\Migration;

/**
 * Class m180906_065114_add_premissions
 */
class m180906_065114_add_premissions extends Migration
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
                ['/by/configs/upgrade', 0, 2, NULL, NULL, NULL, time(), time()],
            ]);
        // 2 设计权限点
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['微信设置', '/by/configs/upgrade'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%auth_item}}',['name'=>"/by/configs/upgrade"]);
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"微信设置"],['child'=>"/by/configs/upgrade"]]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180906_065114_add_premissions cannot be reverted.\n";

        return false;
    }
    */
}
