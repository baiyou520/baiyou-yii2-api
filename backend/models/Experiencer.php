<?php

namespace baiyou\backend\models;

use baiyou\common\components\ActiveRecord;
use Yii;

/**
 * This is the model class for table "experiencer".
 *
 * @property int $experiencer_id 主键
 * @property int $sid sid，来自总后台数据库instance表中instance_id
 * @property string $userstr 添加成功后微信端返回的编号
 * @property string $wechat_id 微信id
 * @property string $name 真实姓名
 * @property int $status 状态:0.解绑，1.绑定
 * @property int $created_at 创建时间戳
 * @property int $updated_at 修改时间戳
 */
class Experiencer extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'experiencer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sid', 'userstr', 'wechat_id', 'status'], 'required'],
            [['sid', 'status', 'created_at', 'updated_at'], 'integer'],
            [['userstr'], 'string', 'max' => 100],
            [['wechat_id'], 'string', 'max' => 100],
            [['name'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'experiencer_id' => '主键',
            'sid' => 'sid，来自总后台数据库instance表中instance_id',
            'userstr' => '添加成功后微信端返回的编号',
            'wechat_id' => '微信id',
            'name' => '真实姓名',
            'status' => '状态:0.解绑，1.绑定',
            'created_at' => '创建时间戳',
            'updated_at' => '修改时间戳',
        ];
    }
}
