<?php

namespace baiyou\common\models;

use Yii;

/**
 * This is the model class for table "instance".
 *
 * @property int $sid sid，来自总后台数据库instance表中instance_id
 * @property int $user_id user_id，来自总后台数据库user表中的id
 * @property string $name 实例名称，如：百优甄选
 * @property string $thumb 实例头像，取值微信小程序图标
 * @property int $certificate_flag 是否认证，0：未认证，1：已认证
 * @property string $level 实例级别，如：初级版
 * @property int $expired_at 到期时间
 * @property string $applet_appid 微信小程序id
 * @property string $applet_appsecret 微信小程序密钥
 * @property int $is_bind 是否绑定，0：未绑定，1：绑定
 * @property string $experience_qrcode 体验版二维码
 * @property string $online_qrcode 上线小程序码
 * @property int $status 0:已关闭，1:正常
 * @property int $created_at 时间戳，创建时间
 * @property int $updated_at 时间戳，修改时间
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
            [['sid', 'user_id', 'expired_at'], 'required'],
            [['sid', 'user_id', 'certificate_flag', 'expired_at', 'is_bind', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['thumb'], 'string', 'max' => 255],
            [['level'], 'string', 'max' => 20],
            [['applet_appid'], 'string', 'max' => 18],
            [['applet_appsecret'], 'string', 'max' => 32],
            [['experience_qrcode', 'online_qrcode'], 'string', 'max' => 100],
            [['sid'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'sid' => 'sid，来自总后台数据库instance表中instance_id',
            'user_id' => 'user_id，来自总后台数据库user表中的id',
            'name' => '实例名称，如：百优甄选',
            'thumb' => '实例头像，取值微信小程序图标',
            'certificate_flag' => '是否认证，0：未认证，1：已认证',
            'level' => '实例级别，如：初级版',
            'expired_at' => '到期时间',
            'applet_appid' => '微信小程序id',
            'applet_appsecret' => '微信小程序密钥',
            'is_bind' => '是否绑定，0：未绑定，1：绑定',
            'experience_qrcode' => '体验版二维码',
            'online_qrcode' => '上线小程序码',
            'status' => '0:已关闭，1:正常',
            'created_at' => '时间戳，创建时间',
            'updated_at' => '时间戳，修改时间',
        ];
    }
}
