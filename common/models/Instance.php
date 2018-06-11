<?php

namespace baiyou\common\models;
use baiyou\backend\models\User;
use Yii;

/**
 * This is the model class for table "instance".
 *
 * @property int $instance_id id 自增
 * @property int $app_id 所属应用id
 * @property int $user_id 购买者id
 * @property string $name 实例名称，如：百优甄选
 * @property int $certificate_flag 是否认证,0:未认证，1:已认证
 * @property int $level 实例等级：0：未认证 1：初级，2：中级，3：高级
 * @property string $applet_appid 微信小程序APPID
 * @property string $applet_appsecret 微信小程序密钥
 * @property int $expired_at 到期时间
 * @property int $status 0:已关闭，1:正常
 * @property int $created_at 时间戳，创建时间
 * @property int $updated_at 时间戳，修改时间
 *
 * @property User $user
 */
class Instance extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'instance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['instance_id', 'app_id', 'user_id', 'name', 'expired_at'], 'required'],
            [['instance_id', 'app_id', 'user_id', 'certificate_flag', 'level', 'expired_at', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 20],
            [['applet_appid'], 'string', 'max' => 18],
            [['applet_appsecret'], 'string', 'max' => 32],
            [['instance_id'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'instance_id' => 'id 自增',
            'app_id' => '所属应用id',
            'user_id' => '购买者id',
            'name' => '实例名称，如：百优甄选',
            'certificate_flag' => '是否认证,0:未认证，1:已认证',
            'level' => '实例等级：0：未认证 1：初级，2：中级，3：高级',
            'applet_appid' => '微信小程序APPID',
            'applet_appsecret' => '微信小程序密钥',
            'expired_at' => '到期时间',
            'status' => '0:已关闭，1:正常',
            'created_at' => '时间戳，创建时间',
            'updated_at' => '时间戳，修改时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
