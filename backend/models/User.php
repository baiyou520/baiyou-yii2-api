<?php
namespace baiyou\backend\models;

use baiyou\common\models\JwtModel;

/**
 * User model 必须和数据库字段保持一致
 *
 * @property integer $id
 * @property string $username
 * @property string $name
 * @property string $avatar_thumb
 * @property string $avatar
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $access_token_expired_at JWT认证(用于api)	过期时间
 * @property string $email
 * @property string $auth_key
 * @property string $phone
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 * @property string $last_login_at 登录时间
 * @property string $last_login_ip 登录ip
 */
class User extends JwtModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash', 'email'], 'required'],
            [['access_token_expired_at', 'last_login_at'], 'safe'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'auth_key'], 'string', 'max' => 32],
            [['avatar_thumb', 'avatar'], 'string', 'max' => 100],
            [['name', 'phone'], 'string', 'max' => 20],
            [['password_hash', 'password_reset_token', 'email'], 'string', 'max' => 255],
            [['last_login_ip'], 'string', 'max' => 14],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
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
