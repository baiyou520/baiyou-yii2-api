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
        ob_start();
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB COMMENT 'Demo示例表'";
        }

        // 1. 建表（主外键）
        $this->createTable('{{%demo}}',[
            'demo_id'       => $this->primaryKey()->unsigned()->comment('主键'),
            'sid'               => $this->integer()->unsigned()->notNull()->comment('sid，来自总后台数据库instance表中instance_id'),
            'name'              => $this->string(30)->notNull()->comment('名称'),
            'avatar'            => $this->integer()->unsigned()->notNull()->comment('头像地址,来自media表media_id'),
            'pics'              => $this->string(255)->notNull()->defaultValue('')->comment('图片集合，json格式，来自media表media_id，如[1,2,3]'),
            'call_no'           => $this->integer()->notNull()->comment('调用次数'),
            'status'            => $this->tinyInteger(1)->notNull()->defaultValue(1)->comment('0:已关闭，1:正常'),
            'created_at'        => $this->integer()->notNull()->defaultValue(0)->comment('时间戳，创建时间'),
            'updated_at'        => $this->integer()->notNull()->defaultValue(0)->comment('时间戳，修改时间'),
            'FOREIGN KEY ([[sid]]) REFERENCES instance ([[sid]]) ON DELETE NO ACTION ON UPDATE NO ACTION',
            'FOREIGN KEY ([[avatar]]) REFERENCES media ([[media_id]]) ON DELETE NO ACTION ON UPDATE NO ACTION'
        ],$tableOptions);

        // 2.gii生成模型（前后端（backend，和frontend）都用的model，放到common->models下面，参考Demo.php）

        // 3.创建控制器：尽量用名词复数

        // 4.添加路由 （当然这里的前提是控制器的名称为 DemoController）
        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['/v1/demo/create', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/v1/demo/delete', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/v1/demo/index', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/v1/demo/update', 0, 2, NULL, NULL, NULL, time(), time()],
                ['/v1/demo/view', 0, 2, NULL, NULL, NULL, time(), time()],
            ]);

        // 5.添加权限点
        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['Demo演示修改', 0, 2, 'L3Demo演示修改', NULL, NULL, time(), time()],
                ['Demo演示删除', 0, 2, 'L3Demo演示删除', NULL, NULL, time(), time()],
                ['Demo演示新增', 0, 2, 'L3Demo演示新增', NULL, NULL, time(), time()],
                ['Demo演示查看', 0, 2, 'L2Demo演示查看，包括列表和详情', NULL, NULL, time(), time()],
                ['Demo演示管理', 0, 2, 'L1Demo演示管理权限点集合', NULL, NULL, time(), time()],
            ]);

        // 6.分配权限点
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['应用', 'Demo演示管理'], // L0
                    ['Demo演示管理', 'Demo演示修改'], // L1
                    ['Demo演示管理', 'Demo演示删除'], // L1
                    ['Demo演示管理', 'Demo演示新增'], // L1
                    ['Demo演示管理', 'Demo演示查看'], // L1
                        ['Demo演示查看', '/v1/demo/index'], // L2
                        ['Demo演示查看', '/v1/demo/view'], // L2
                            ['Demo演示新增', '/v1/demo/create'], // L3
                            ['Demo演示删除', '/v1/demo/delete'], // L3
                            ['Demo演示修改', '/v1/demo/update'], // L3
            ]);
        // 6.1 分配权限给角色
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['user', 'Demo演示管理']
            ]);
        /*****创建菜单
         * 0级菜单暂时固定有两个，分别为：1：L0-System 和 2：L0-Application
         * 开发者需要创建1级菜单和2级菜单
         */

        // 7. 创建1级菜单，注意：指定父级菜单parent为2,即属于L0-Application;主键id指定为一个数据库中没有的值，请设置1000以上（这块有待完善）
        $this->insert('{{%menu}}', [
            'id' => 11,
            'name' => 'L1-Demo',
            'parent' => 2,
            'route' => '/v1/demo/index',
            'data' => '{"icon":"icon-list","text":"Demo演示管理"}'
        ]);

        // 8. 创建2级菜单，注意：指定父级菜单parent为11,即父级菜单的id;主键id指定为一个数据库中没有的值，请设置1000以上（这块有待完善）
        $this->insert('{{%menu}}', [
            'id' => 12,
            'name' => 'L2-DemoMgr',
            'parent' => 11,
            'route' => '/v1/demo/index',
            'data' => '{"link":"/demo/list","text":"Demo列表"}'
        ]);

        // 9. main.php 配置url

        // 10. 去控制器中，完善个性化需求的接口


        // 以下这步不用做,未测试
        if(YII_ENV_PROD){
            $this->delete('{{%menu}}',['id' => [11, 12]]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%demo}}');
        $this->delete('{{%auth_item}}',['like','name','demo']);
        $this->delete('{{%auth_item}}',['like','name','Demo%',false]); // 单边like https://www.yiichina.com/topic/6062
        $this->delete('{{%auth_item_child}}',['like','parent','Demo%',false]);
        $this->delete('{{%auth_item_child}}',['like','child','Demo%',false]);
        $this->delete('{{%menu}}',['id' => [11, 12]]);
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
