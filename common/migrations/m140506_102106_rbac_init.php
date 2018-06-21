<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\base\InvalidConfigException;
use yii\rbac\DbManager;

/**
 * Initializes RBAC tables.
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class m140506_102106_rbac_init extends \yii\db\Migration
{
    /**
     * @throws yii\base\InvalidConfigException
     * @return DbManager
     */
    protected function getAuthManager()
    {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof DbManager) {
            throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }

        return $authManager;
    }

    /**
     * @return bool
     */
    protected function isMSSQL()
    {
        return $this->db->driverName === 'mssql' || $this->db->driverName === 'sqlsrv' || $this->db->driverName === 'dblib';
    }

    protected function isOracle()
    {
        return $this->db->driverName === 'oci';
    }

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $authManager = $this->getAuthManager();
        $this->db = $authManager->db;

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($authManager->ruleTable, [
            'name' => $this->string(64)->notNull(),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY ([[name]])',
        ], $tableOptions);

        $this->createTable($authManager->itemTable, [
            'name' => $this->string(64)->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'description' => $this->text(),
            'rule_name' => $this->string(64),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY ([[name]])',
            'FOREIGN KEY ([[rule_name]]) REFERENCES ' . $authManager->ruleTable . ' ([[name]])' .
            $this->buildFkClause('ON DELETE SET NULL', 'ON UPDATE CASCADE'),
        ], $tableOptions);
        $this->createIndex('idx-auth_item-type', $authManager->itemTable, 'type');

        // 插入初始化数据
//        $this->insert($authManager->itemTable, [
//            'name' => '/*',
//            'type' =>  2,
//            'created_at' => time(),
//            'updated_at' => time()
//        ]);
        //继续
        $this->batchInsert($authManager->itemTable,
            ['name','type','description','rule_name','data','created_at','updated_at'],
            [
                ['/*', 2, NULL, NULL, NULL, time(), time()],
                ['/by/auth/login', 2, NULL, NULL, NULL, time(), time()],
                ['/by/common/upload-avatar', 2, NULL, NULL, NULL, time(), time()],
                ['/by/customers/index', 2, NULL, NULL, NULL, time(), time()],
                ['/by/customers/update', 2, NULL, NULL, NULL, time(), time()],
                ['/by/customers/view', 2, NULL, NULL, NULL, time(), time()],
                ['/by/dashboard/index', 2, NULL, NULL, NULL, time(), time()],
                ['/by/dashboard/notice', 2, NULL, NULL, NULL, time(), time()],
                ['/by/logs/index', 2, NULL, NULL, NULL, time(), time()],
                ['/by/logs/view', 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/change-password', 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/create', 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/delete', 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/edit-avatar', 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/index', 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/roles', 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/start-up', 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/update', 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/view', 2, NULL, NULL, NULL, time(), time()],
                ['/by/users/profile', 2, NULL, NULL, NULL, time(), time()],
                ['admin', 1, '普通管理员', NULL, NULL, time(), time()],
                ['super_admin', 1, '超级管理员', NULL, NULL, time(), time()],
                ['员工修改(中台)', 2, '修改某个员工', NULL, NULL, time(), time()],
                ['员工删除(中台)', 2, '删除某个员工', NULL, NULL, time(), time()],
                ['员工新增(中台)', 2, '后台直接新增一个员工', NULL, NULL, time(), time()],
                ['员工管理(中台)', 2, '整个员工管理模块权限点集合', NULL, NULL, time(), time()],
                ['基础权限', 2, '各类基础权限集合', NULL, NULL, time(), time()],
                ['客户修改(C端)', 2, '修改微信端客户资料', NULL, NULL, time(), time()],
                ['客户管理(C端)', 2, '整个微信端客户管理权限', NULL, NULL, time(), time()],
                ['查看客户(C端)', 2, '查看客户列表页及详情页', NULL, NULL, time(), time()],
                ['查看用户(中台)', 2, '查看中台管理端的用户', NULL, NULL, time(), time()],
                ['错误日志', 2, '错误日志管理', NULL, NULL, time(), time()]
            ]);





        $this->createTable($authManager->itemChildTable, [
            'parent' => $this->string(64)->notNull(),
            'child' => $this->string(64)->notNull(),
            'PRIMARY KEY ([[parent]], [[child]])',
            'FOREIGN KEY ([[parent]]) REFERENCES ' . $authManager->itemTable . ' ([[name]])' .
            $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
            'FOREIGN KEY ([[child]]) REFERENCES ' . $authManager->itemTable . ' ([[name]])' .
            $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);

        // 插入初始化数据
//        $this->insert($authManager->itemChildTable, [
//            'parent' => 'super_admin',
//            'child' =>  '/*'
//        ]);

        //继续

        $this->batchInsert($authManager->itemChildTable,['parent','child'],[

            ['super_admin', '/*'],
            ['基础权限', '/by/auth/login'],
            ['基础权限', '/by/common/upload-avatar'],
            ['查看客户(C端)', '/by/customers/index'],
            ['客户修改(C端)', '/by/customers/update'],
            ['查看客户(C端)', '/by/customers/view'],
            ['基础权限', '/by/dashboard/index'],
            ['基础权限', '/by/dashboard/notice'],
            ['错误日志', '/by/logs/index'],
            ['错误日志', '/by/logs/view'],
            ['基础权限', '/by/users/change-password'],
            ['员工新增(中台)', '/by/users/create'],
            ['员工删除(中台)', '/by/users/delete'],
            ['基础权限', '/by/users/edit-avatar'],
            ['查看用户(中台)', '/by/users/index'],
            ['基础权限', '/by/users/start-up'],
            ['基础权限', '/by/users/profile'],
            ['员工修改(中台)', '/by/users/update'],
            ['查看用户(中台)', '/by/users/view'],
            ['员工管理(中台)', '员工修改(中台)'],
            ['员工管理(中台)', '员工删除(中台)'],
            ['员工管理(中台)', '员工新增(中台)'],
            ['admin', '员工管理(中台)'],
            ['admin', '基础权限'],
            ['客户管理(C端)', '客户修改(C端)'],
            ['admin', '客户管理(C端)'],
            ['客户管理(C端)', '查看客户(C端)'],
            ['员工管理(中台)', '查看用户(中台)'],
            ['admin', '错误日志']

        ]);



        $this->createTable($authManager->assignmentTable, [
            'item_name' => $this->string(64)->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'created_at' => $this->integer(),
            'PRIMARY KEY ([[item_name]], [[user_id]])',
            'FOREIGN KEY ([[item_name]]) REFERENCES ' . $authManager->itemTable . ' ([[name]])' .
            $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
            'FOREIGN KEY ([[user_id]]) REFERENCES user ([[id]])' .
            $this->buildFkClause('ON DELETE NO ACTION', 'ON UPDATE NO ACTION'),
        ], $tableOptions);

        // 插入初始化数据
        $this->insert($authManager->assignmentTable, [
            'user_id' => 1,
            'item_name' => 'super_admin',
            'created_at' => time()
        ]);
        $this->insert($authManager->assignmentTable, [
            'user_id' => 2,
            'item_name' => 'admin',
            'created_at' => time()
        ]);


        if ($this->isMSSQL()) {
            $this->execute("CREATE TRIGGER dbo.trigger_auth_item_child
            ON dbo.{$authManager->itemTable}
            INSTEAD OF DELETE, UPDATE
            AS
            DECLARE @old_name VARCHAR (64) = (SELECT name FROM deleted)
            DECLARE @new_name VARCHAR (64) = (SELECT name FROM inserted)
            BEGIN
            IF COLUMNS_UPDATED() > 0
                BEGIN
                    IF @old_name <> @new_name
                    BEGIN
                        ALTER TABLE {$authManager->itemChildTable} NOCHECK CONSTRAINT FK__auth_item__child;
                        UPDATE {$authManager->itemChildTable} SET child = @new_name WHERE child = @old_name;
                    END
                UPDATE {$authManager->itemTable}
                SET name = (SELECT name FROM inserted),
                type = (SELECT type FROM inserted),
                description = (SELECT description FROM inserted),
                rule_name = (SELECT rule_name FROM inserted),
                data = (SELECT data FROM inserted),
                created_at = (SELECT created_at FROM inserted),
                updated_at = (SELECT updated_at FROM inserted)
                WHERE name IN (SELECT name FROM deleted)
                IF @old_name <> @new_name
                    BEGIN
                        ALTER TABLE {$authManager->itemChildTable} CHECK CONSTRAINT FK__auth_item__child;
                    END
                END
                ELSE
                    BEGIN
                        DELETE FROM dbo.{$authManager->itemChildTable} WHERE parent IN (SELECT name FROM deleted) OR child IN (SELECT name FROM deleted);
                        DELETE FROM dbo.{$authManager->itemTable} WHERE name IN (SELECT name FROM deleted);
                    END
            END;");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $authManager = $this->getAuthManager();
        $this->db = $authManager->db;

        if ($this->isMSSQL()) {
            $this->execute('DROP TRIGGER dbo.trigger_auth_item_child;');
        }

        $this->dropTable($authManager->assignmentTable);
        $this->dropTable($authManager->itemChildTable);
        $this->dropTable($authManager->itemTable);
        $this->dropTable($authManager->ruleTable);
    }

    protected function buildFkClause($delete = '', $update = '')
    {
        if ($this->isMSSQL()) {
            return '';
        }

        if ($this->isOracle()) {
            return ' ' . $delete;
        }

        return implode(' ', ['', $delete, $update]);
    }
}
