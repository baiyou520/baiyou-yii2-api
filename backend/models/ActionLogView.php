<?php

namespace baiyou\backend\models;

use baiyou\common\components\ActiveRecord;
use Yii;

/**
 * This is the model class for table "action_log_view".
 *
 * @property string $message 操作内容
 * @property int $created_at 创建时间戳
 * @property string $name 姓名(昵称)
 * @property int $sid sid，来自总后台数据库instance表中instance_id
 * @property int $status 状态:0,给开发人员看，1，给客户看
 * @property string $module 操作模块
 */
class ActionLogView extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'action_log_view';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message'], 'string'],
            [['created_at', 'sid', 'status'], 'integer'],
            [['name', 'module'], 'required'],
            [['name'], 'string', 'max' => 30],
            [['module'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'message' => '操作内容',
            'created_at' => '创建时间戳',
            'name' => '姓名(昵称)',
            'sid' => 'sid，来自总后台数据库instance表中instance_id',
            'status' => '状态:0,给开发人员看，1，给客户看',
            'module' => '操作模块',
        ];
    }
}
