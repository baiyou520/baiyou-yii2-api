<?php

namespace baiyou\common\models;


/**
 * This is the model class for table "customer".
 *
 * @property int $customer_id id自增
 * @property string $username 用户名(即登录名)
 * @property string $avatar 微信头像
 * @property string $nickname 微信昵称
 * @property string $name 姓名(真实姓名，比如取自地址)
 * @property string $openid 微信移动端标识符
 * @property string $access_token_expired_at JWT认证(用于api)
 * @property string $phone  手机号(比如取自地址)
 * @property int $parent_id  推荐人id
 * @property int $status 状态
 * @property string $last_login_at  最后登录时间
 * @property string $last_login_ip 最后登录ip
 * @property int $created_at 时间戳，创建时间
 * @property int $updated_at 时间戳，修改时间
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
            [['username', 'name', 'openid'], 'required'],
            [['access_token_expired_at', 'last_login_at'], 'safe'],
            [['parent_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'name', 'phone'], 'string', 'max' => 20],
            [['avatar'], 'string', 'max' => 255],
            [['nickname'], 'string', 'max' => 50],
            [['openid'], 'string', 'max' => 28],
            [['last_login_ip'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'customer_id' => 'id自增',
            'username' => '用户名(即登录名)',
            'avatar' => '微信头像',
            'nickname' => '微信昵称',
            'name' => '姓名(真实姓名，比如取自地址)',
            'openid' => '微信移动端标识符',
            'access_token_expired_at' => 'JWT认证(用于api)',
            'phone' => ' 手机号(比如取自地址)',
            'parent_id' => ' 推荐人id',
            'status' => '状态',
            'last_login_at' => ' 最后登录时间',
            'last_login_ip' => '最后登录ip',
            'created_at' => '时间戳，创建时间',
            'updated_at' => '时间戳，修改时间',
        ];
    }
}
