<?php

use yii\db\Migration;

/**
 * Class m180917_092933_add_prem_config_menu_shop
 */
class m180917_092933_add_prem_config_menu_shop extends Migration
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
                ['/by/configs/create', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/by/configs/store-info', 0, 2, NULL, NULL, NULL, time(), time()], // 辅助路由，没有实际action，用于生成菜单
                ['门店信息', 0, 2, NULL, NULL, NULL, time(), time()],

            ]);
        // 2 设计权限点
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['设置', '门店信息'],
                ['门店信息', '/by/configs/index'],
                ['门店信息', '/by/configs/create'],
                ['门店信息', '/by/configs/store-info'],
            ]);

        // 3 添加菜单
        $this->batchInsert('{{%menu}}',
            ['id','name','parent','route','order','data'],
            [
                [17, 'L2-StoreMgr', 6, '/by/configs/store-info', 1, '{"link":"/setting/store","text":"门店信息"}'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%auth_item}}',['name'=>"/by/configs/create"]);
        $this->delete('{{%auth_item}}',['name'=>"/by/configs/store-info"]);
        $this->delete('{{%auth_item}}',['name'=>"门店信息"]);

        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"设置"],['child'=>"门店管理"]]);
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"门店信息"],['child'=>"/by/configs/index"]]);
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"门店信息"],['child'=>"/by/configs/create"]]);
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"门店信息"],['child'=>"/by/configs/store-info"]]);

        $this->delete('{{%menu}}',['id'=>17]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180917_092933_add_prem_config_menu_shop cannot be reverted.\n";

        return false;
    }
    */
}
