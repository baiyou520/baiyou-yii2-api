<?php

namespace baiyou\common\models;

use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "customer".
 *
 * @property int $id 主键
 * @property string $username 用户名(即登录名)
 * @property string $avatar 大头像(原图)
 * @property string $nickname 微信昵称
 * @property string $language 语言
 * @property string $province 省份
 * @property string $country 国家
 * @property string $city 城市
 * @property int $gender 性别，1：男，2:女,0:未知
 * @property string $name 真实姓名(比如取自订单地址)
 * @property string $phone 电话
 * @property int $status 激活状态:10为启用，0位禁用
 * @property string $last_login_at 最后登录时间
 * @property string $last_login_ip 最后登录ip
 * @property string $auth_key yii2认证key
 * @property string $password_hash 密码
 * @property int $created_at 创建时间戳
 * @property int $updated_at 修改时间戳
 *
 */
class Customer extends JwtModel
{
    public function behaviors()
    {
        return [
            [
                /**
                 * TimestampBehavior：
                 * 创建的时候，默认插入当前时间戳给created_at和updated_at字段
                 * 更新的时候，默认更新当前时间戳给updated_at字段
                 */
                'class'              => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => time(),
            ],
            [
                /**
                 * AttributeBehavior：
                 * 由于返回乘以了1000，修改的时候又不会复写crated_at，而前端又可能会传过来,故在此再除以1000
                 */
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'created_at',
                ],
                'value' => function ($event) {
                    if (strlen($this->created_at) === 13)
                        return $this->created_at / 1000;
                    else
                        return $this->created_at;
                },
            ],
            [
                /**
                 * AttributeBehavior：
                 * 由于前端框架处理10位时间戳比较麻烦，故在此乘以1000
                 */
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_AFTER_FIND => 'created_at',
                ],
                'value' => function ($event) {
                    return $this->created_at * 1000;
                },
            ],
            [
                /**
                 * AttributeBehavior：
                 * 由于前端框架处理10位时间戳比较麻烦，故在此乘以1000
                 */
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_AFTER_FIND => 'updated_at',
                ],
                'value' => function ($event) {
                    return $this->updated_at * 1000;
                },
            ],
            [
                /**
                 * ActionLogBehavior：
                 * 操作日志
                 */
                'class' => 'baiyou\common\components\ActionLogBehavior',
            ],

        ];
    }
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
            [['username', 'nickname', 'name'], 'required'],
            [['gender', 'status', 'created_at', 'updated_at'], 'integer'],
            [['last_login_at'], 'safe'],
            [['username'], 'string', 'max' => 100],
            [['avatar', 'password_hash'], 'string', 'max' => 255],
            [['nickname', 'name'], 'string', 'max' => 50],
            [['language', 'phone'], 'string', 'max' => 20],
            [['province', 'country', 'city'], 'string', 'max' => 30],
            [['last_login_ip'], 'string', 'max' => 15],
            [['auth_key'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'username' => '用户名(即登录名)',
            'avatar' => '大头像(原图)',
            'nickname' => '微信昵称',
            'language' => '语言',
            'province' => '省份',
            'country' => '国家',
            'city' => '城市',
            'gender' => '性别，1：男，2:女,0:未知',
            'name' => '真实姓名(比如取自订单地址)',
            'phone' => '电话',
            'status' => '激活状态:10为启用，0位禁用',
            'last_login_at' => '最后登录时间',
            'last_login_ip' => '最后登录ip',
            'auth_key' => 'yii2认证key',
            'password_hash' => '密码',
            'created_at' => '创建时间戳',
            'updated_at' => '修改时间戳',
        ];
    }
    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return \Yii::$app->security->validatePassword($password, $this->password_hash);
    }
    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = \Yii::$app->security->generatePasswordHash($password);
    }
    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = \Yii::$app->security->generateRandomString();
    }
}
