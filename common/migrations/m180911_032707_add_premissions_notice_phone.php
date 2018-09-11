<?php

use yii\db\Migration;

/**
 * Class m180911_032707_add_premissions_notice_phone
 */
class m180911_032707_add_premissions_notice_phone extends Migration
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
                ['/by/configs/set-notice-phone', 0, 2, NULL, NULL, NULL, time(), time()],
            ]);
        // 2 设计权限点
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['微信设置', '/by/configs/set-notice-phone'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%auth_item}}',['name'=>"/by/configs/set-notice-phone"]);
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"微信设置"],['child'=>"/by/configs/set-notice-phone"]]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180911_032707_add_premissions_notice_phone cannot be reverted.\n";

        return false;
    }
    */
}
