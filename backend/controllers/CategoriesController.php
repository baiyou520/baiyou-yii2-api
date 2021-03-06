<?php
/**
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/8/15
 * Time: 上午10:11
 */

namespace baiyou\backend\controllers;

use baiyou\backend\models\Category;
use baiyou\common\components\BaseErrorCode;
use baiyou\common\components\Helper;
use baiyou\common\models\Instance;
use Yii;
use yii\db\Query;

class CategoriesController extends BaseController
{
    public $modelClass = 'baiyou\backend\models\Category';
    public function actions()
    {
        $actions = parent::actions();

        // 禁用动作
        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * 分类接口
     * @return array|yii\db\ActiveRecord[]
     * @params  symbol=goods(商品分类)/
     * @author nwh@caiyoudata.com
     * @time 2018/6/27 17:58
     */
    public function actionIndex(){
        $params=Yii::$app->request->get();
        $symbol=isset($params['symbol'])?$params['symbol']:"";
        $type=isset($params['type'])?$params['type']:"";
        $models = Category::find()
            ->andWhere(['symbol' => $symbol])
            ->orderBy('sort asc,category_id asc')
            ->all();

        //如果查询 微信导航
        if($symbol == 'navigation_link'){
            $instance_info = Instance::find()->where(['sid'=>Helper::getSid()])->one();
            $level = json_decode($instance_info['level'],true);
            if ($level != ''&& $level['name'] == 2){       //企业官网版
                //如果是企业官网型    导航的分类只显示  首页和微页面
                $count = count($models);
                $navigation_link_list = [];
                for($m=0;$m<$count;$m++){
                    if($models[$m]['name'] == "首页" || $models[$m]['name'] == "微页面" ){
                        $navigation_link_list[] = $models[$m];
                    }
                }
                $models = $navigation_link_list;
            }
        }

        if(!empty($models)){
            if(empty($type)){
                $models=yii\helpers\ArrayHelper::toArray($models);
                $models = Helper::generateTree($models, 0, 'category_pid', 'category_id');
            }
            return $models;
        }else{
            return ["message"=>"分类参数symbol错误","code"=>BaseErrorCode::$PARAMS_ERROR];
        }
    }

    public function actionView($id){
        $category=Category::find()->where(['category_id'=>$id])->one();
        if(empty($category)){
            return ["message"=>"Object not found: ".$id,"code"=>BaseErrorCode::$OBJECT_NOT_FOUND];
        }
        return $category;
    }

    //分类添加
    public function actionCreate(){
        $request=Yii::$app->request;
        $params=$request->post();
        //判断是否存在父级id内容
        if(isset($params['category_pid'])){
            $p_category=Category::find()->where(['symbol'=>$params['symbol']])
                ->andWhere(['category_id'=>$params['category_pid']])
                ->one();
            //父级id不为0时判断
            if($params['category_pid']!=0&&empty($p_category)){
                return ["message"=>"父级id错误","code"=>BaseErrorCode::$PARAMS_ERROR];
            }
        }else{
            $params['category_pid']=0;
        }
        $category_have=Category::find()->andWhere(['symbol'=>$params['symbol'],'name'=>$params['name']])->one();
        if(!empty($category_have)){
            return ["message"=>"该内容已创建,请检查","code"=>BaseErrorCode::$PARAMS_ERROR,"data"=>"新增内容重复"];
        }
        $category=new Category();
        //补充sid
        $params['sid']=Helper::getSid();
        $category->load($params, '');
        if ($category->save()) {
            return ["message"=>"添加成功","code"=>1];
        }else{
            return ["message"=>"添加失败","code"=>BaseErrorCode::$PARAMS_ERROR,"data"=>$category->errors];
        }
    }
}