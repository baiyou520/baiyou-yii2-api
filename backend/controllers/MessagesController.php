<?php
/**
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/10/30
 * Time: 下午5:03
 */

namespace baiyou\backend\controllers;


use baiyou\backend\models\Message;
use baiyou\common\components\BaseErrorCode;
use baiyou\common\components\CreateQueryHelper;
use yii\data\ActiveDataProvider;
use Yii;
class MessagesController extends BaseController
{
    public $modelClass = 'baiyou\backend\models\Message';
    public function actions()
    {
        $actions = parent::actions();

        // 禁用动作
        unset($actions['index']);
        unset($actions['create']);
//        unset($actions['update']);
//        unset($actions['delete']);
        return $actions;
    }

    /**
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/10/30 下午5:55
     */
    public function actionIndex(){
        $sort = \Yii::$app->request->get('sort','');
        $query = CreateQueryHelper::createQuery($this->modelClass);
        CreateQueryHelper::addOrderSort($sort, 'message', $query);
        $data = \Yii::$app->request->get();
        if (isset($data['type']) && $data['type'] == 1){ // 个人回复
            $provider = new ActiveDataProvider([
                'query' => $query->andFilterWhere(['=','user_id',Yii::$app->user->id])
            ]);
        }else{
            $provider = new ActiveDataProvider([
                'query' => $query
            ]);
        }

        $data = ['list' => $provider->getModels(),'pagination'=>['total' => $provider->getTotalCount()]];
        return  ['message' => '获得回复成功','code' => 1,'data' => $data];
    }

    /**
     * @return array
     * @author sft@caiyoudata.com
     * @time  adt
     */
    public function actionCreate(){
        $data=Yii::$app->request->post();
        $data['user_id'] = Yii::$app->user->id;
        $message = new Message();
        $message->load($data,'');
        if(!$message->save()){
            return ["message"=>"新增失败","code"=>BaseErrorCode::$PARAMS_ERROR,"data"=>$message->errors];
        }else{
            return ["message"=>"新增成功","code"=>1];
        }
    }
}