<?php

namespace baiyou\backend\models;

use Yii;

/**
 * This is the model class for table "auth_assignment".
 *
 * @property string $item_name
 * @property int $sid
 * @property int $user_id
 * @property int $created_at
 *
 * @property AuthItem $itemName
 * @property User $user
 */
class AuthAssignment extends \baiyou\common\components\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_assignment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['item_name', 'sid', 'user_id'], 'required'],
            [['sid', 'user_id', 'created_at'], 'integer'],
            [['item_name'], 'string', 'max' => 64],
            [['item_name', 'sid', 'user_id'], 'unique', 'targetAttribute' => ['item_name', 'sid', 'user_id']],
            [['item_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['item_name' => 'name']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'item_name' => 'Item Name',
            'sid' => 'Sid',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemName()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'item_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
