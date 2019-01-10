<?php
/**
 * 微信前端api基础控制器
 * User: billyshen
 * Date: 2018/6/1
 * Time: 上午10:51
 */

namespace baiyou\frontend\controllers;

use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\CompositeAuth;
use baiyou\common\components\CookiesAuth;

class BaseController extends ActiveController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                CookiesAuth::className(),
            ],

        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index'  => ['get'],
                'view'   => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
            ],
        ];

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*']
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;//加了之后需要登录认证
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];//加了之后 需要登录认证

        // 微信端用户存在customer表中，暂时不启用权限控制
//        $behaviors['access'] = [
//            'class' => AccessControl::className(),
//        ];
        return $behaviors;
    }

    /**
     * 格式化列表页返回
     * @return array
     * @author  billyshen 2018/6/5 上午10:32
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),[
            'index' => [
                'class' => 'baiyou\common\components\IndexAction'
            ]
        ]);
    }
}