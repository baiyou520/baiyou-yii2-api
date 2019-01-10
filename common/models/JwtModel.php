<?php
/**
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/6/1
 * Time: 下午1:33
 */

namespace baiyou\common\models;

use baiyou\common\components\ActiveRecord;
use baiyou\common\components\Helper;
use Yii;
use yii\db\Expression;
use Firebase\JWT\JWT;
use yii\web\IdentityInterface;
use yii\web\Request as WebRequest;
use yii\behaviors\TimestampBehavior;

class JwtModel extends \baiyou\common\components\ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * @var string to store JSON web token
     */
    public $access_token;


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }


    /**
     * {@inheritdoc}
     */
    public function getId()
    {
//        return $this->getPrimaryKey(); // 因为多了一个sid，所以必须直接指定id，不然/yii2/web/User.php 256行报错
        return $this->id;
    }
    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    /**
     * Generate access token
     *  This function will be called every on request to refresh access token.
     *
     * @param bool $forceRegenerate whether regenerate access token even if not expired
     *
     * @return bool whether the access token is generated or not
     */
    public function generateAccessTokenAfterUpdatingClientInfo($forceRegenerate=false)
    {
        // update client login, ip
        $this->last_login_ip = Yii::$app->request->userIP;
        $this->last_login_at = new Expression('NOW()');

        // check time is expired or not
        if($forceRegenerate)
        {
            // generate access token
            $this->generateAccessToken();
        }
        $this->save(false);
        return true;
    }

    public function generateAccessToken(){
        // generate access token
        // $this->access_token = Yii::$app->security->generateRandomString();
        $tokens = $this->getJWT();
        $this->access_token = $tokens[0];   // Token
//        $this->access_token_expired_at = date("Y-m-d H:i:s", $tokens[1]['exp']); // Expire

        $cookies = Yii::$app->response->cookies;
        // 创建sso登录所需的cookies值
        $cookies->add(new \yii\web\Cookie([
            'name' => 'access-token',
            'value' => $this->access_token,
            'domain' => '.baiyoudata.com',
            'httpOnly' => true,
//            'expire' => time() + 70 * 24 * 60 * 60, // 70天过期  永不过期
        ]));
    }

    /*
     * JWT Related Functions
     * sft added
     */

    /**
     * Store JWT token header items.
     * @var array
     */
    protected static $decodedToken;

    protected static function getSecretKey()
    {
        return Yii::$app->params['jwtSecretCode'];
    }

    // And this one if you wish
    protected static function getHeaderToken()
    {
        return [];
    }
    /**
     * Logins user by given JWT encoded string. If string is correctly decoded
     * - array (token) must contain 'jti' param - the id of existing user
     * @param  string $accessToken access token to decode
     * @return mixed|null          User model or null if there's no user
     * @throws \yii\web\ForbiddenHttpException if anything went wrong
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $secret = static::getSecretKey();
        // Decode token and transform it into array.
        // Firebase\JWT\JWT throws exception if token can not be decoded
        try {
            $decoded = JWT::decode($token, $secret, [static::getAlgo()]);
        } catch (\Exception $e) {
            return false;
        }
        static::$decodedToken = (array) $decoded;
        // If there's no jti param - exception
        if (!isset(static::$decodedToken['jti'])) {
            return false;
        }
        // JTI is unique identifier of user.
        // For more details: https://tools.ietf.org/html/rfc7519#section-4.1.7
        $id = static::$decodedToken['jti'];
        return static::findByJTI($id);
    }
    /**
     * Finds User model using static method findOne
     * Override this method in model if you need to complicate id-management
     * @param  string $id if of user to search
     * @return mixed       User model
     */
    public static function findByJTI($id)
    {
        /** @var User $user */
        if (self::tableName() == '{{%user}}'){ // 中台SSO，不需要access_token_expired_at
            $user = static::find()->where([
                '=', 'id', $id
            ])->andWhere([
                '=', 'sid',  Helper::getSid()
            ])
            ->andWhere([
                '=', 'status',  self::STATUS_ACTIVE
            ])->one();
        }else{ // 微信端仍旧需要
            $user = static::find()->where([
                '=', 'id', $id
            ])
            ->andWhere([
                '=', 'status',  self::STATUS_ACTIVE
            ])->one();
//            ->andWhere([
//                '>', 'access_token_expired_at', new Expression('NOW()')  // 微信端使用，即永不过期，因为不会做判断
//            ])->one();
        }

        return $user;
    }

    /**
     * Getter for encryption algorytm used in JWT generation and decoding
     * Override this method to set up other algorytm.
     * @return string needed algorytm
     */
    public static function getAlgo()
    {
        return 'HS256';
    }

    /**
     * Returns some 'id' to encode to token. By default is current model id.
     * If you override this method, be sure that findByJTI is updated too
     * @return integer any unique integer identifier of user
     */
    public function getJTI()
    {
        return $this->getId();
    }

    /**
     * Encodes model data to create custom JWT with model.id set in it
     * @return array encoded JWT
     */
    public function getJWT()
    {
        // Collect all the data
        $secret      = static::getSecretKey();
        $currentTime = time();
//        $expire      = $currentTime + 24 * 60 * 60; // 微信端不过期
        $request     = Yii::$app->request;
        $hostInfo    = '';
        // There is also a \yii\console\Request that doesn't have this property
        if ($request instanceof WebRequest) {
            $hostInfo = $request->hostInfo;
        }

        // Merge token with presets not to miss any params in custom
        // configuration
        $token = array_merge([
            'iat' => $currentTime,      // Issued at: timestamp of token issuing.
            'iss' => $hostInfo,         // Issuer: A string containing the name or identifier of the issuer application. Can be a domain name and can be used to discard tokens from other applications.
            'aud' => $hostInfo,
            'nbf' => $currentTime,       // Not Before: Timestamp of when the token should start being considered valid. Should be equal to or greater than iat. In this case, the token will begin to be valid 10 seconds
//            'exp' => $expire,           // 微信端不过期 Expire: Timestamp of when the token should cease to be valid. Should be greater than iat and nbf. In this case, the token will expire 60 seconds after being issued.
            'data' => [
                'username'      =>  $this->username,
                'lastLoginAt'   =>  $this->last_login_at,
            ]
        ], static::getHeaderToken());
        // Set up id
        $token['jti'] = $this->getJTI();    // JSON Token ID: A unique string, could be used to validate a token, but goes against not having a centralized issuer authority.
        return [JWT::encode($token, $secret, static::getAlgo()), $token];
    }
}