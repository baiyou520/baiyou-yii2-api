<?php

namespace baiyou\backend\models;

use baiyou\common\models\Instance;
use Yii;

/**
 * This is the model class for table "notice_user".
 *
 * @property int $notice_user_id 主键
 * @property int $sid sid，来自总后台数据库instance表中instance_id
 * @property int $notice_id 通知id
 * @property int $user_id 用户id
 * @property int $is_read 是否已读，1:已读，0:未读
 * @property int $created_at 创建时间戳
 * @property int $updated_at 修改时间戳
 *
 * @property Instance $s
 * @property Notice $notice
 * @property User $user
 */
class NoticeUser extends \baiyou\common\components\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notice_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sid', 'notice_id'], 'required'],
            [['sid', 'notice_id', 'user_id', 'is_read', 'created_at', 'updated_at'], 'integer'],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Instance::className(), 'targetAttribute' => ['sid' => 'sid']],
            [['notice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Notice::className(), 'targetAttribute' => ['notice_id' => 'notice_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'notice_user_id' => '主键',
            'sid' => 'sid，来自总后台数据库instance表中instance_id',
            'notice_id' => '通知id',
            'user_id' => '用户id',
            'is_read' => '是否已读，1:已读，0:未读',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotice()
    {
        return $this->hasOne(Notice::className(), ['notice_id' => 'notice_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
