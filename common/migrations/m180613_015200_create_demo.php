<?php

use yii\db\Migration;

/**
 * Class m180613_015200_create_demo
 */
class m180613_015200_create_demo extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB COMMENT 'Demo示例表'";
        }

        // 1. 建表（主外键）
        $this->createTable('{{%demo}}',[
            'demo_id'       => $this->primaryKey()->unsigned()->comment('主键'),
            'name'              => $this->string(30)->notNull()->comment('名称'),
            'avatar'            => $this->string(200)->notNull()->defaultValue('https://img.zcool.cn/community/01786557e4a6fa0000018c1bf080ca.png@2o.png')->comment('头像地址'),
            'call_no'           => $this->integer()->notNull()->comment('调用次数'),
            'status'            => $this->tinyInteger(1)->notNull()->defaultValue(1)->comment('0:已关闭，1:正常'),
            'created_at'        => $this->integer()->notNull()->defaultValue(0)->comment('时间戳，创建时间'),
            'updated_at'        => $this->integer()->notNull()->defaultValue(0)->comment('时间戳，修改时间')

        ],$tableOptions);

        // 2.gii生成模型（前后端（backend，和frontend）都用的model，放到common->models下面，参考Demo.php）

        // 3.创建控制器：尽量用名词复数

        // 4.添加路由 （当然这里的前提是控制器的名称为 DemoController）
        $this->batchInsert('{{%auth_item}}',
            ['name','type','description','rule_name','data','created_at','updated_at'],
            [
                ['/v1/demo/create', 2, NULL, NULL, NULL, time(), time()],
                ['/v1/demo/delete', 2, NULL, NULL, NULL, time(), time()],
                ['/v1/demo/index', 2, NULL, NULL, NULL, time(), time()],
                ['/v1/demo/update', 2, NULL, NULL, NULL, time(), time()],
                ['/v1/demo/view', 2, NULL, NULL, NULL, time(), time()],
            ]);

        // 5.添加权限点
        $this->batchInsert('{{%auth_item}}',
            ['name','type','description','rule_name','data','created_at','updated_at'],
            [
                ['Demo演示修改', 2, 'Demo演示修改', NULL, NULL, time(), time()],
                ['Demo演示删除', 2, 'Demo演示删除', NULL, NULL, time(), time()],
                ['Demo演示新增', 2, 'Demo演示新增', NULL, NULL, time(), time()],
                ['Demo演示查看', 2, 'Demo演示查看，包括列表和详情', NULL, NULL, time(), time()],
                ['Demo演示管理', 2, 'Demo演示管理权限点集合', NULL, NULL, time(), time()],
            ]);

        // 6.分配权限点
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['Demo演示新增', '/v1/demo/create'],
                ['Demo演示删除', '/v1/demo/delete'],
                ['Demo演示查看', '/v1/demo/index'],
                ['Demo演示修改', '/v1/demo/update'],
                ['Demo演示查看', '/v1/demo/view'],
                ['Demo演示管理', 'Demo演示修改'],
                ['Demo演示管理', 'Demo演示删除'],
                ['Demo演示管理', 'Demo演示新增'],
                ['Demo演示管理', 'Demo演示查看'],
                ['admin', 'Demo演示管理']
            ]);

        /*****创建菜单
         * 0级菜单暂时固定有两个，分别为：1：L0-System 和 2：L0-Application
         * 开发者需要创建1级菜单和2级菜单
         */

        // 7. 创建1级菜单，注意：指定父级菜单parent为2,即属于L0-Application;主键id指定为一个数据库中没有的值（这块有待完善）
        $this->insert('{{%menu}}', [
            'id' => 5,
            'name' => 'L1-Demo',
            'parent' => 2,
            'route' => '/v1/demo/index',
            'data' => '{"icon":"icon-list","text":"Demo演示管理"}'
        ]);

        // 8. 创建2级菜单，注意：指定父级菜单parent为5,即1级菜单的id;主键id指定为一个数据库中没有的值（这块有待完善）
        $this->insert('{{%menu}}', [
            'id' => 9,
            'name' => 'L2-DemoMgr',
            'parent' => 5,
            'route' => '/v1/demo/index',
            'data' => '{"link":"/demo/list","text":"Demo列表"}'
        ]);

        // 9. main.php 配置url

        // 10. 去控制器中，完善个性化需求的接口


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%demo}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180613_015200_create_demo cannot be reverted.\n";

        return false;
    }
    */
}
