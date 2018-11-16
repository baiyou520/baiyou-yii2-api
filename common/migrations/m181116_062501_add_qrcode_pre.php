<?php

use yii\db\Migration;

/**
 * Class m181116_062501_add_qrcode_pre
 */
class m181116_062501_add_qrcode_pre extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['/by/dashboard/update-online-qr-code', 0, 2, NULL, NULL, NULL, time(), time()],
            ]);

        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['首页', '/by/dashboard/update-online-qr-code'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181116_062501_add_qrcode_pre cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181116_062501_add_qrcode_pre cannot be reverted.\n";

        return false;
    }
    */
}
