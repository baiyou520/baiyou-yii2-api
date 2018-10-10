<?php

namespace baiyou\backend\models;

use Yii;

/**
 * This is the model class for table "category".
 *
 * @property int $category_id 主键
 * @property int $sid sid，来自总后台数据库instance表中instance_id
 * @property int $category_pid 父级id
 * @property string $symbol 标识
 * @property string $thumb 分类图标
 * @property string $name 名称
 * @property int $sort 排序
 * @property string $data 其他配置项JSON编码
 * @property int $created_at 创建时间戳
 * @property int $updated_at 修改时间戳
 *
 * @property Media[] $media
 */
class Category extends \baiyou\common\components\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['symbol', 'name'], 'required'],
            [['sid', 'category_pid', 'sort', 'created_at', 'updated_at'], 'integer'],
            [['data'], 'string'],
            [['symbol', 'name'], 'string', 'max' => 20],
            [['thumb'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'category_id' => '主键',
            'sid' => 'sid，来自总后台数据库instance表中instance_id',
            'category_pid' => '父级id',
            'symbol' => '标识',
            'thumb' => '分类图标',
            'name' => '名称',
            'sort' => '排序',
            'data' => '其他配置项JSON编码',
            'created_at' => '创建时间戳',
            'updated_at' => '修改时间戳',
        ];
    }

    /**
     * @return mixed
     */
    public function getMedia()
    {
        return $this->hasMany(Media::className(), ['group_id' => 'category_id'])->onCondition(['media.status' => 1]);
    }

    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information
        unset($fields['sid']);
        $fields_from_other_tables = [
            'counts' => function($model) {
                if($model->symbol === 'pic_group') {
                    return count($model->media);
                }else{
                    return 0;
                }
            }
        ];
        return array_merge($fields,$fields_from_other_tables);
    }
}
