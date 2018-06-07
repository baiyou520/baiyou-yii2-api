<?php
/**
 * 日志控制器
 * User: billyshen
 * Date: 2018/6/4
 * Time: 上午10:10
 */

namespace baiyou\backend\controllers;


class LogsController extends BaseController
{
    public $modelClass = 'backend\modules\v1\models\Log';

    public function actions()
    {
        $actions = parent::actions();

//        $actions['index'] =  [
//            'class' => 'yii\rest\IndexAction',
//            'modelClass' => $this->modelClass,
//            'dataFilter'=>['class' => 'yii\data\ActiveDataFilter',
//                'searchModel'=>['class'=>'backend\modules\v1\models\Log']]
//        ];
        return $actions;
    }
}