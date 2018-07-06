<?php
/**
 * 利用顶级域名的cookie，实现单点登录，token取自cookies
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/7/5
 * Time: 下午5:35
 */

namespace baiyou\common\components;


use yii\filters\auth\AuthMethod;

class CookiesAuth extends AuthMethod
{
    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {

        $cookies = \Yii::$app->request->cookies;
        $token =  $cookies->getValue('access-token');
        if ($token){
            $identity = $user->loginByAccessToken($token);
            if ($identity === null) {
                $this->challenge($response);
                $this->handleFailure($response);
            }
            return $identity;

        }

        return null;
    }
}