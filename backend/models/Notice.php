<?php

namespace baiyou\backend\models;

use baiyou\common\models\Instance;
use Yii;

/**
 * This is the model class for table "notice".
 *
 * @property int $notice_id 主键
 * @property int $sid sid，来自总后台数据库instance表中instance_id
 * @property int $user_id 用户id
 * @property string $title 标题
 * @property string $content 内容
 * @property int $type  类型：1:通知,2:消息,3:待办
 * @property string $related_route 跳转用的相关路由
 * @property int $related_id 跳转到的对应ID
 * @property string $tips  额外提示
 * @property int $tips_level  额外提示程度：1:todo,2:urgent,3:doing,4:processing
 * @property int $status 是否已读，1:已读，0:未读
 * @property int $created_at 创建时间戳
 * @property int $updated_at 修改时间戳
 *
 * @property Instance $s
 * @property User $user
 */
class Notice extends \baiyou\common\components\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notice';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sid', 'user_id', 'content'], 'required'],
            [['sid', 'user_id', 'type', 'related_id', 'tips_level', 'status', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 50],
            [['related_route'], 'string', 'max' => 100],
            [['tips'], 'string', 'max' => 20],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Instance::className(), 'targetAttribute' => ['sid' => 'sid']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'notice_id' => '主键',
            'sid' => 'sid，来自总后台数据库instance表中instance_id',
            'user_id' => '用户id',
            'title' => '标题',
            'content' => '内容',
            'type' => ' 类型：1:通知,2:消息,3:待办',
            'related_route' => '跳转用的相关路由',
            'related_id' => '跳转到的对应ID',
            'tips' => ' 额外提示',
            'tips_level' => ' 额外提示程度：1:todo,2:urgent,3:doing,4:processing',
            'status' => '是否已读，1:已读，0:未读',
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
