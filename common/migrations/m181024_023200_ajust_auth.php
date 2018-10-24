<?php

use yii\db\Migration;

/**
 * Class m181024_023200_ajust_auth
 */
class m181024_023200_ajust_auth extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1.添加路由
        $this->update('{{%auth_item}}',['name'=>'设置管理'],['name'=>'设置']);
        $this->update('{{%auth_item}}',['name'=>'微信管理'],['name'=>'客户管理']);
        $this->update('{{%auth_item_child}}',['parent'=>'微信管理'],['and' ,['parent'=>"设置管理"],['child'=>"微信设置"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'微信管理'],['and' ,['parent'=>"设置管理"],['child'=>"微信高级设置"]]);
        $this->update('{{%menu}}',['data'=>'{"link":"/customer/mgr","text":"客户管理"}'],['id'=>7]);
        $this->update('{{%auth_item_child}}',['parent'=>'微信管理'],['and' ,['parent'=>"系统"],['child'=>"客户管理"]]);
        $this->batchInsert('{{%auth_item}}',
            ['name','sid','type','description','rule_name','data','created_at','updated_at'],
            [
                ['客服消息', 0, 2, 'L2设置微信客服欢迎语', NULL, NULL, time(), time()],
            ]);
        $this->update('{{%auth_item_child}}',['parent'=>'客服消息'],['and' ,['parent'=>"客户管理"],['child'=>"/by/customers/upload-temp-media"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'客服消息'],['and' ,['parent'=>"客户管理"],['child'=>"/by/customers/welcome"]]);
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['微信管理', '客服消息']
            ]);
        $this->update('{{%auth_item_child}}',['parent'=>'微信管理'],['and' ,['parent'=>"客户管理"],['child'=>"模板消息"]]);
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"微信管理"],['child'=>"客户修改"]]);
        $this->delete('{{%auth_item_child}}',['and' ,['parent'=>"微信管理"],['child'=>"客户查看"]]);
        $this->delete('{{%auth_item}}',['name'=>'员工修改']);
        $this->delete('{{%auth_item}}',['name'=>'员工新增']);
        $this->delete('{{%auth_item}}',['name'=>'员工删除']);
        $this->delete('{{%auth_item}}',['name'=>'角色修改']);
        $this->delete('{{%auth_item}}',['name'=>'角色新增']);
        $this->delete('{{%auth_item}}',['name'=>'角色删除']);
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['员工管理', '/by/users/update'],
                ['员工管理', '/by/users/create'],
                ['员工管理', '/by/users/delete'],
                ['角色管理', '/by/authorities/update-role'],
                ['角色管理', '/by/authorities/create'],
                ['角色管理', '/by/authorities/delete-role'],
            ]);
        $this->update('{{%auth_item}}',['name'=>'权限管理'],['name'=>'角色管理']);
        $this->update('{{%auth_item}}',['name'=>'权限查看'],['name'=>'角色查看']);
        $this->update('{{%auth_item_child}}',['parent'=>'系统'],['and' ,['parent'=>"员工管理"],['child'=>"/by/users/update"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'系统'],['and' ,['parent'=>"员工管理"],['child'=>"/by/users/create"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'系统'],['and' ,['parent'=>"员工管理"],['child'=>"/by/users/delete"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'系统'],['and' ,['parent'=>"权限管理"],['child'=>"/by/authorities/update-role"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'系统'],['and' ,['parent'=>"权限管理"],['child'=>"/by/authorities/create"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'系统'],['and' ,['parent'=>"权限管理"],['child'=>"/by/authorities/delete-role"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'设置管理'],['and' ,['parent'=>"员工管理"],['child'=>"员工查看"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'设置管理'],['and' ,['parent'=>"权限管理"],['child'=>"权限查看"]]);
        $this->delete('{{%auth_item}}',['name'=>'员工管理']);
        $this->delete('{{%auth_item}}',['name'=>'权限管理']);
        $this->batchInsert('{{%auth_item_child}}',
            ['parent','child'],
            [
                ['操作日志', '/by/users/index'],
            ]);
        $this->update('{{%auth_item_child}}',['parent'=>'super_admin'],['and' ,['parent'=>"系统"],['child'=>"/by/users/update"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'super_admin'],['and' ,['parent'=>"系统"],['child'=>"/by/users/create"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'super_admin'],['and' ,['parent'=>"系统"],['child'=>"/by/users/delete"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'super_admin'],['and' ,['parent'=>"系统"],['child'=>"/by/authorities/update-role"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'super_admin'],['and' ,['parent'=>"系统"],['child'=>"/by/authorities/create"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'super_admin'],['and' ,['parent'=>"系统"],['child'=>"/by/authorities/delete-role"]]);

        $this->update('{{%auth_item_child}}',['parent'=>'super_admin'],['and' ,['parent'=>"微信高级设置"],['child'=>"/by/configs/set-applet-secret"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'super_admin'],['and' ,['parent'=>"微信高级设置"],['child'=>"/by/configs/set-notice-phone"]]);
        $this->update('{{%auth_item_child}}',['parent'=>'super_admin'],['and' ,['parent'=>"微信高级设置"],['child'=>"/by/configs/upload-cert"]]);
        $this->delete('{{%auth_item}}',['name'=>'微信高级设置']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181024_023200_ajust_auth cannot be reverted.\n";

        return false;
    }
    */
}
