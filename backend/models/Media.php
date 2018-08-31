<?php

namespace baiyou\backend\models;

use baiyou\common\models\Instance;
use Yii;

/**
 * This is the model class for table "media".
 *
 * @property int $media_id 主键
 * @property string $name 资源名称
 * @property string $url 地址
 * @property int $type 类型：1.图片2.音频3.视频
 * @property int $group_id 分组id,CG表S=pic_group/S=audio_group/S=video_group
 * @property int $sid sid，来自总后台数据库instance表中instance_id
 * @property int $created_at 创建时间戳
 * @property int $updated_at 修改时间戳
 *
 * @property Instance $s
 * @property Category $group
 */
class Media extends \baiyou\common\components\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'media';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'url', 'type', 'group_id', 'sid'], 'required'],
            [['type', 'group_id', 'sid', 'created_at', 'updated_at'], 'integer'],
            [['name', 'url'], 'string', 'max' => 100],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Instance::className(), 'targetAttribute' => ['sid' => 'sid']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['group_id' => 'category_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'media_id' => '主键',
            'name' => '资源名称',
            'url' => '地址',
            'type' => '类型：1.图片2.音频3.视频',
            'group_id' => '分组id,CG表S=pic_group/S=audio_group/S=video_group',
            'sid' => 'sid，来自总后台数据库instance表中instance_id',
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
    public function getGroup()
    {
        return $this->hasOne(Category::className(), ['category_id' => 'group_id']);
    }

    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information
        unset($fields['sid']);
        $fields_from_other_tables = [
            'url' => function($model) {
                return 'https://'.Yii::$app->params['img_server']['domain'].'/'.$model->url;
            },
            'thumb_url' => function($model) {
                return 'https://'.Yii::$app->params['img_server']['domain'].'/'.$model->url.'_240x240';
            },
            'image_size' => function($model) {//需求返回图片像素大小
                $res=GetImageSize('https://'.Yii::$app->params['img_server']['domain'].'/'.$model->url);
                return $res[0].'*'.$res[1];
            },
        ];
        return array_merge($fields,$fields_from_other_tables);
    }
}
