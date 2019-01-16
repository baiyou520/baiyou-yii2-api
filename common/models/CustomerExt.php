<?php

namespace baiyou\common\models;

use Yii;
/**
 * This is the model class for table "customer_ext".
 *
 * @property int $customer_ext_id 主键
 * @property int $customer_id 用户与店铺关系表
 * @property int $sid 所属店铺id
 * @property string $openid 微信移动端标识符
 * @property int $parent_id 推荐人id
 * @property int $created_at 创建时间戳
 * @property int $updated_at 修改时间戳
 *
 * @property Customer $customer
 * @property Instance $s
 */
class CustomerExt extends \baiyou\common\components\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_ext';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id'], 'required'],
            [['customer_id', 'sid', 'parent_id', 'created_at', 'updated_at'], 'integer'],
            [['openid'], 'string', 'max' => 28],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer_id' => 'id']],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Instance::className(), 'targetAttribute' => ['sid' => 'sid']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'customer_ext_id' => '主键',
            'customer_id' => '用户与店铺关系表',
            'sid' => '所属店铺id',
            'openid' => '微信移动端标识符',
            'parent_id' => '推荐人id',
            'created_at' => '创建时间戳',
            'updated_at' => '修改时间戳',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Instance::className(), ['sid' => 'sid']);
    }
}
