<?php

namespace baiyou\common\models;

use baiyou\backend\models\ActionLog;
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
 * @property int $is_bind 是否绑定，0：未绑定，1：绑定
 * @property int $expired_at 到期时间
 * @property string $applet_appid 微信小程序id
 * @property string $applet_appsecret 微信小程序密钥
 * @property string $experience_qrcode 体验版二维码
 * @property string $online_qrcode 上线小程序码
 * @property string $wx_mch_id 微信支付分配的商户号
 * @property string $wx_mch_key 商户平台设置的密钥key
 * @property string $ssl_cert_path cert证书地址
 * @property string $ssl_key_path key证书地址
 * @property string $merchant_id   子商户id
 * @property int $logo_pic   子商户logo
 * @property string $logo_url   子商户logourl 提交到微信
 * @property int $protocol_pic 授权函图片ID
 * @property string $protocol 授权函图片ID 提交到微信
 * @property int $agreement_pic 营业执照或个体工商户营业执照彩照或扫描件
 * @property string $agreement_media_id 营业执照或个体工商户营业执照彩照或扫描件 提交到微信
 * @property int $operator_pic 营业执照内登记的经营者身份证彩照或扫描件
 * @property string $operator_media_id 营业执照内登记的经营者身份证彩照或扫描件 提交到微信
 * @property int $status 0:试用，1:正常，-1:过期，-2:删除
 * @property int $created_at 时间戳，创建时间
 * @property int $updated_at 时间戳，修改时间
 *
 * @property ActionLog[] $actionLogs
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
            [['sid', 'user_id', 'certificate_flag', 'is_bind', 'expired_at', 'status','logo_pic','protocol_pic','operator_pic', 'agreement_pic',  'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['logo_url'], 'string', 'max' => 255],
            [['protocol','agreement_media_id','operator_media_id'], 'string', 'max' => 100],
            [['merchant_id'], 'string', 'max' => 25],
            [['thumb'], 'string', 'max' => 255],
            [['level'], 'string', 'max' => 255],
            [['applet_appid'], 'string', 'max' => 18],
            [['applet_appsecret', 'wx_mch_key'], 'string', 'max' => 32],
            [['experience_qrcode', 'online_qrcode'], 'string', 'max' => 100],
            [['wx_mch_id'], 'string', 'max' => 10],
            [['ssl_cert_path', 'ssl_key_path'], 'string', 'max' => 40],
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
            'is_bind' => '是否绑定，0：未绑定，1：绑定',
            'expired_at' => '到期时间',
            'applet_appid' => '微信小程序id',
            'applet_appsecret' => '微信小程序密钥',
            'experience_qrcode' => '体验版二维码',
            'online_qrcode' => '上线小程序码',
            'wx_mch_id' => '微信支付分配的商户号',
            'wx_mch_key' => '商户平台设置的密钥key',
            'ssl_cert_path' => 'cert证书地址',
            'ssl_key_path' => 'key证书地址',
            'merchant_id' => '子商户id',
            'logo_pic' => '子商户logo',
            'logo_url' => '子商户logo_url 提交到微信',
            'protocol_pic' => '授权函图片ID',
            'protocol' => '授权函 提交到微信',
            'agreement_pic' => '营业执照或个体工商户营业执照彩照或扫描件',
            'agreement_media_id' => '营业执照或个体工商户营业执照彩照或扫描件 提交到微信',
            'operator_pic' => '营业执照内登记的经营者身份证彩照或扫描件',
            'operator_media_id' => '营业执照内登记的经营者身份证彩照或扫描件 提交到微信',
            'status' => '0:试用，1:正常，-1:过期，-2:删除',
            'created_at' => '时间戳，创建时间',
            'updated_at' => '时间戳，修改时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActionLogs()
    {
        return $this->hasMany(ActionLog::className(), ['sid' => 'sid']);
    }
}