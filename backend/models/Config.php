<?php

namespace baiyou\backend\models;

/**
 * This is the model class for table "config".
 *
 * @property int $config_id 主键
 * @property string $symbol 标识
 * @property string $content 值
 * @property int $encode 值的编码形式1:string,2:json,3:int
 * @property int $created_at 创建时间戳
 * @property int $updated_at 修改时间戳
 * @property int $sid
 */
class Config extends \baiyou\common\components\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'config';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['symbol', 'content'], 'required'],
            [['content'], 'string'],
            [['encode', 'created_at', 'updated_at', 'sid'], 'integer'],
            [['symbol'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'config_id' => '主键',
            'symbol' => '标识',
            'content' => '值',
            'encode' => '值的编码形式1:string,2:json,3:int',
            'created_at' => '创建时间戳',
            'updated_at' => '修改时间戳',
            'sid' => 'Sid',
        ];
    }
}
