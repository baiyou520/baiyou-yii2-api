<?php

namespace baiyou\common\models;


/**
 * This is the model class for table "customer".
 *
 * @property int $id 主键
 * @property int $sid sid，来自总后台数据库instance表中instance_id
 * @property string $username 用户名(即登录名)
 * @property string $avatar 大头像(原图)
 * @property string $nickname 微信昵称
 * @property string $language 语言
 * @property string $province 省份
 * @property string $country 国家
 * @property string $city 城市
 * @property int $gender 性别，1：男，2:女
 * @property string $name 真实姓名(比如取自订单地址)
 * @property string $openid 微信移动端标识符
 * @property string $phone 电话
 * @property int $parent_id 推荐人id
 * @property string $source_from 注册来源
 * @property int $status 激活状态:10为启用，0位禁用
 * @property string $last_login_at 最后登录时间
 * @property string $last_login_ip 最后登录ip
 * @property int $created_at 创建时间戳
 * @property int $updated_at 修改时间戳
 *
 * @property Instance $s
 */
class Customer extends JwtModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sid', 'username', 'nickname', 'language', 'province', 'country', 'city', 'gender', 'name', 'openid'], 'required'],
            [['sid', 'gender', 'parent_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['last_login_at'], 'safe'],
            [['username', 'source_from'], 'string', 'max' => 100],
            [['avatar'], 'string', 'max' => 255],
            [['nickname', 'name'], 'string', 'max' => 50],
            [['language', 'phone'], 'string', 'max' => 20],
            [['province', 'country', 'city'], 'string', 'max' => 30],
            [['openid'], 'string', 'max' => 28],
            [['last_login_ip'], 'string', 'max' => 15],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Instance::className(), 'targetAttribute' => ['sid' => 'sid']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'sid' => 'sid，来自总后台数据库instance表中instance_id',
            'username' => '用户名(即登录名)',
            'avatar' => '大头像(原图)',
            'nickname' => '微信昵称',
            'language' => '语言',
            'province' => '省份',
            'country' => '国家',
            'city' => '城市',
            'gender' => '性别，1：男，2:女',
            'name' => '真实姓名(比如取自订单地址)',
            'openid' => '微信移动端标识符',
            'phone' => '电话',
            'parent_id' => '推荐人id',
            'source_from' => '注册来源',
            'status' => '激活状态:10为启用，0位禁用',
            'last_login_at' => '最后登录时间',
            'last_login_ip' => '最后登录ip',
            'created_at' => '创建时间戳',
            'updated_at' => '修改时间戳',
        ];
    }
}
