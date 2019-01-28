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
 * @property string $storage_value 储值金额
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
//            [['storage_value'], 'number'],
            [['openid'], 'string', 'max' => 28],
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
            'storage_value' => '储值金额',
            'created_at' => '创建时间戳',
            'updated_at' => '修改时间戳',
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Instance::className(), ['sid' => 'sid']);
    }
}
