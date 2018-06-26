<?php
/**
 * 日志控制器
 * User: billyshen
 * Date: 2018/6/4
 * Time: 上午10:10
 */

namespace baiyou\backend\controllers;


use baiyou\backend\models\Log;
use yii\data\ActiveDataProvider;

class LogsController extends BaseController
{
    public $modelClass = 'baiyou\backend\models\Log';

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index']);
        return $actions;
    }

    /**
     * 获取用户列表数据
     * @return array
     * @author  billyshen 2018/5/28 下午8:24
     */
    public function actionIndex(){


        $provider = new ActiveDataProvider([
                'query' => Log::find()->orderBy('id desc')
        ]);

        // 获取分页和排序数据
        $models = $provider->getModels();

        // 在当前页获取数据项的数目
        $count = $provider->getCount();

        // 获取所有页面的数据项的总数
        $totalCount = $provider->getTotalCount();
        $data = ['list' => $models,'pagination'=>['total' => $totalCount]];
        return  ['message' => '获取用户列表成功','code' => 1,'data' => $data];
    }
}