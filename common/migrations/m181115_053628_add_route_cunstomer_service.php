<?php

use yii\db\Migration;

/**
 * Class m181115_053628_add_route_cunstomer_service
 */
class m181115_053628_add_route_cunstomer_service extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['/by/messages/get-customer-service-user', 0, 2, NULL, NULL, NULL, time(), time()],
            ]);

        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['客服消息', '/by/messages/get-customer-service-user'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('auth_item_child',['parent'=>'客服消息','child'=>'/by/messages/get-customer-service-user']);
        $this->delete('auth_item',['name'=>'/by/messages/get-customer-service-user']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181115_053628_add_route_cunstomer_service cannot be reverted.\n";

        return false;
    }
    */
}
