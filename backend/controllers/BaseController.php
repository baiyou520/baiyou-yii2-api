<?php
/**
 * 基类，用户处理权限认证等,绝大部分控制器需要继承这个控制器，以达到权限控制的目的
 * User: billyshen
 * Date: 2018/5/28
 * Time: 下午3:42
 */

namespace baiyou\backend\controllers;

use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use mdm\admin\components\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\CompositeAuth;

class BaseController extends ActiveController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
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

        $behaviors['access'] = [//给角色判断权限用的
            'class' => AccessControl::className(),
        ];
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
                'class' => 'baiyou\common\frame\IndexAction'
            ]
        ]);
    }

}