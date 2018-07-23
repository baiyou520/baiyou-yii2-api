<?php
/**
 * 日志控制器
 * User: billyshen
 * Date: 2018/6/4
 * Time: 上午10:10
 */

namespace baiyou\backend\controllers;


use baiyou\backend\models\ActionLog;
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
        $request=\Yii::$app->request;
        $parms=$request->get();
//
        $keyword=isset($parms['keyword'])?$parms['keyword']:"";// 类型/消息体

        $begin=isset($parms['log_time'])?$parms['log_time'][0]:"";//查找时间范围开始
        $end=isset($parms['log_time'])?$parms['log_time'][1]:"";//时间范围结束
        $begin = strlen($begin) === 13 ? $begin/1000 : $begin;   // ng-alain 1.1.2暂时只支持13位时间戳，我们数据库存的是10位
        $end = strlen($end) === 13 ? $end/1000 : $end;

        $level=isset($parms['level'])?$parms['level']:"";//日志等级
        $provider = new ActiveDataProvider([
                'query' => Log::find()->orderBy('id desc')
                    ->andFilterWhere(['like','category',$keyword])
                    ->orFilterWhere(['like','message',$keyword])
                    ->andFilterWhere(['>=','log_time',$begin])
                    ->andFilterWhere(['<=','log_time',$end])
                    ->andFilterWhere(['level'=>$level])
        ]);



        // 获取分页和排序数据
        $models = $provider->getModels();

        // 在当前页获取数据项的数目
        $count = $provider->getCount();

        // 获取所有页面的数据项的总数
        $totalCount = $provider->getTotalCount();
        $data = ['list' => $models,'pagination'=>['total' => $totalCount]];

        ActionLog::add('执行了一次查询错误日志数据库，得到了'.$totalCount.'条数据','设置模块'); // 测试操作日志，比较重要的日志需要记录，参考https://help.youzan.com/displaylist/detail_4_11697
        return  ['message' => '获取错误日志列表成功','code' => 1,'data' => $data];
    }
}