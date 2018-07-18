<?php
/**
 * 设置相关控制器
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/7/18
 * Time: 上午9:50
 */

namespace baiyou\backend\controllers;


class ConfigsController extends BaseController
{
    public $modelClass = 'baiyou\backend\models\Config';

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index']);
        return $actions;
    }

}