<?php
/**
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/9/17
 * Time: 下午7:09
 */

namespace baiyou\frontend\controllers;


use baiyou\backend\models\Config;
use baiyou\common\components\BaseErrorCode;

class ConfigsController extends BaseController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();

        // 禁用动作
        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['delete']);
        unset($actions['update']);
        return $actions;
    }

    /**
     * 获取某配置内容
     * @return mixed
     * @time 2018/7/9 14:43
     */
    public function actionIndex(){
        $params=\Yii::$app->request->get();
        $symbol=isset($params['symbol'])?$params['symbol']:"";
        $configs=Config::findOne(['symbol'=>$params['symbol']]);
        if(!empty($configs)){
            if($configs['encode']==2){
                $configs['content']=json_decode(($configs['content']),true);
            }
            return ["message"=>"OK","code"=>1,"data"=>$configs];
        }else{
            return ["message"=>"请检查参数symbol是否正确","code"=>BaseErrorCode::$PARAMS_ERROR];
        }
    }
}