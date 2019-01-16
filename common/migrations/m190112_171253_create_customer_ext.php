<?php

use yii\db\Migration;

/**
 * Class m190112_094624_create_customer_ext
 */
class m190112_171253_create_customer_ext extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB  COMMENT="运费模板"';
        }
        //用户店铺关系表
        $this->createTable('{{%customer_ext}}', [
            'customer_ext_id' => $this->primaryKey(11)->unsigned()->comment('主键'),
            'customer_id'     => $this->integer()->unsigned()->notNull()->comment('用户与店铺关系表'),
            'sid'        => $this->integer()->unsigned()->notNull()->comment('所属店铺id'),
            'openid'     => $this->string(28)->notNull()->defaultValue('')->comment('微信移动端标识符'),
            'parent_id'  => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('推荐人id'),
            'created_at' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('创建时间戳'),
            'updated_at' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('修改时间戳'),
            'FOREIGN KEY([[customer_id]]) REFERENCES `customer`([[id]]) ON DELETE NO ACTION ON UPDATE NO ACTION',
            'FOREIGN KEY([[sid]]) REFERENCES `instance`([[sid]]) ON DELETE NO ACTION ON UPDATE NO ACTION',
            ], $tableOptions);

        //用户表补充字段
        $this->addColumn('customer','auth_key','VARCHAR(32) NOT NULL DEFAULT "" COMMENT "yii2认证key" AFTER `last_login_ip`');
        $this->addColumn('customer','password_hash','VARCHAR(255) NOT NULL DEFAULT "" COMMENT "密码" AFTER `auth_key`');

        $this->alterColumn('customer','openid','VARCHAR(28) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "" COMMENT "微信移动端标识符"');
        //把现在所有的customer表用户openid信息转移到ext表
        $customers=(new \yii\db\Query())->from('customer')->all();
        $cus_ext=[];
        foreach($customers as $value){
            if(!empty($custom=(new \yii\db\Query())->from('customer')->where(['openid'=>$value['openid']])->andWhere(['<','sid',$value['sid']])->orderBy('id asc')->one())){
                $value['sid']=$custom['sid'];
            }
            $cus_ext[]=[
                'customer_id'=>$value['id'],
                'sid'        =>$value['sid'],
                'openid'     =>$value['openid'],
                'parent_id'  =>$value['parent_id'],
                'created_at'  =>$value['created_at'],
                'updated_at'  =>$value['updated_at'],
            ];
        }
        $fields=['customer_id','sid','openid','parent_id','created_at','updated_at'];
        $code=\Yii::$app->db->createCommand()->batchInsert('customer_ext', $fields,$cus_ext)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('customer_ext');
        $this->dropTable('customer_ext');
        $this->dropColumn('customer','password_hash');
        $this->dropColumn('customer','auth_key');
        $this->alterColumn('customer','openid','VARCHAR(28) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT "微信移动端标识符"');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190112_094624_create_customer_ext cannot be reverted.\n";

        return false;
    }
    */
}
