<?php

use mdm\admin\components\Configs;

/**
 * Migration table of table_menu
 * 
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class m140602_111327_create_menu_table extends yii\db\Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $menuTable = Configs::instance()->menuTable;
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($menuTable, [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull(),
            'parent' => $this->integer(),
            'route' => $this->string(),
            'order' => $this->integer(),
            'data' => $this->binary(),
            "FOREIGN KEY ([[parent]]) REFERENCES {$menuTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
        ], $tableOptions);

//        INSERT INTO `menu` VALUES
//    (1,'L1-Customer',8,'/by/customers/index',NULL,'{"icon":"icon-user","text":"客户管理"}'),
//        (2,'L2-UserMgr',4,'/by/users/index',NULL,'{"link":"/customers/index","text":"员工管理"}'),
//        (3,'L2-LogMgr',4,'/by/logs/index',NULL,'{"link":"/setting/log","text":"错误日志"}'),
//        (4,'L1-Setting',8,'/by/logs/index',NULL,'{"icon":"anticon anticon-setting","text":"设置管理"}'),
//        (5,'L2-CustMgr',1,'/by/customers/index',NULL,'{"link":"/customers/index","text":"客户管理"}'),
//        (6,'L1-Demo',9,'/v1/demo/index',NULL,'{"icon":"icon-list","text":"Demo演示管理"}'),
//        (7,'L2-DemoMgr',6,'/v1/demo/index',NULL,'{"link":"/demo/list","text":"Demo列表"}'),
//        (8,'L0-System',NULL,'/by/dashboard/index',NULL,'{"text":"系统","group":"true"}'),
//        (9,'L0-Application',NULL,'/by/dashboard/index',NULL,'{"text":"应用","group":"true"}');
        $this->insert('{{%menu}}', [
            'id' => 1,
            'name' => 'L0-System',
            'parent' => NULL,
            'route' => '/by/dashboard/index',
            'data' => '{"text":"系统","group":"true"}'
        ]);

        $this->insert('{{%menu}}', [
            'id' => 2,
            'name' => 'L0-Application',
            'parent' => NULL,
            'route' => '/by/dashboard/index',
            'data' => '{"text":"应用","group":"true"}'
        ]);

        $this->insert('{{%menu}}', [
            'id' => 3,
            'name' => 'L1-Customer',
            'parent' => 1,
            'route' => '/by/customers/index',
            'data' => '{"icon":"icon-user","text":"客户管理"}'
        ]);

        $this->insert('{{%menu}}', [
            'id' => 4,
            'name' => 'L1-Setting',
            'parent' => 1,
            'route' => '/by/logs/index',
            'data' => '{"icon":"anticon anticon-setting","text":"设置管理"}'
        ]);


        $this->insert('{{%menu}}', [
            'id' => 5,
            'name' => 'L1-Demo',
            'parent' => 2,
            'route' => '/v1/demo/index',
            'data' => '{"icon":"icon-list","text":"Demo演示管理"}'
        ]);

        $this->insert('{{%menu}}', [
            'id' => 6,
            'name' => 'L2-UserMgr',
            'parent' => 4,
            'route' => '/by/users/index',
            'data' => '{"link":"/customers/index","text":"员工管理"}'
        ]);
        $this->insert('{{%menu}}', [
            'id' => 7,
            'name' => 'L2-LogMgr',
            'parent' => 4,
            'route' => '/by/logs/index',
            'data' => '{"link":"/setting/log","text":"错误日志"}'
        ]);

        $this->insert('{{%menu}}', [
            'id' =>8,
            'name' => 'L2-CustMgr',
            'parent' => 3,
            'route' => '/by/customers/index',
            'data' => '{"link":"/customers/index","text":"客户管理"}'
        ]);

        $this->insert('{{%menu}}', [
            'id' => 9,
            'name' => 'L2-DemoMgr',
            'parent' => 5,
            'route' => '/v1/demo/index',
            'data' => '{"link":"/demo/list","text":"Demo列表"}'
        ]);


    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable(Configs::instance()->menuTable);
    }
}
