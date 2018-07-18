<?php
/**
 * 微信端用户管理控制器
 * User: billyshen
 * Date: 2018/6/2
 * Time: 上午9:15
 */

namespace baiyou\backend\controllers;

use Yii;
use yii\db\Query;
use baiyou\common\components\ActiveDataProvider;

class CustomersController extends BaseController
{
    public $modelClass = 'baiyou\common\models\Customer';

    public function actions()
    {
        $actions = parent::actions();
        // 禁用动作
        unset($actions['index']);
        unset($actions['delete']);
        unset($actions['create']);
//        unset($actions['view']);
//        unset($actions['update']);
        return $actions;
    }

    /**
     * 获取微信端用户列表数据
     * @return array
     * @author  billyshen 2018/6/2 上午9:26
     */
    public function actionIndex()
    {
        $query = new Query();
        $request = Yii::$app->request;
        $parms = $request->get();
        $keyword = isset($parms['keyword']) ? $parms['keyword'] : "";//昵称/手机号/邮箱
        $begin = isset($parms['c_begin']) ? $parms['c_begin'] : "";//查找时间范围开始
        $end = isset($parms['c_end']) ? $parms['c_end'] : "";//时间范围结束
        $status = isset($parms['status']) ? $parms['status'] : "";//用户状态
        $provider = new ActiveDataProvider([
            'query' =>
                $query->select(['id', 'nickname', 'name', 'avatar', 'last_login_at', 'last_login_ip', 'status', 'phone'])
                    ->from('customer')
                    ->andFilterWhere(['like', 'nickname', $keyword])
                    ->orFilterWhere(['like', 'name', $keyword])
                    ->andFilterWhere(['>=', 'user.created_at', $begin])
                    ->andFilterWhere(['<=', 'user.created_at', $end])
                    ->orderBy('created_at desc'),
            'pagination' => [
                'pageSizeParam' => 'size',
            ]
        ]);

        // 获取分页和排序数据
        $models = $provider->getModels();

        // 在当前页获取数据项的数目
        $count = $provider->getCount();

        // 获取所有页面的数据项的总数
        $totalCount = $provider->getTotalCount();
        $data = ['list' => $models, 'pagination' => ['total' => $totalCount]];
        return ['message' => '获取客户列表成功', 'code' => 1, 'data' => $data];
    }
}