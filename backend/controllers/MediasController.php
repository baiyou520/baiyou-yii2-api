<?php
/**
 * Created by PhpStorm.
 * User: nwh
 * Date: 2018/7/9
 * Time: 17:34
 */

namespace baiyou\backend\controllers;

use baiyou\backend\models\Category;
use baiyou\backend\models\Media;
use baiyou\common\components\BaseErrorCode;
use yii;
use baiyou\common\components\ActiveDataProvider;
use baiyou\common\components\Helper;
class MediasController extends BaseController
{
    public $modelClass = 'baiyou\backend\models';
    public function actions()
    {
        $actions = parent::actions();

        // 禁用动作
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * 图片列表
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2018/7/9 17:51
     */
    public function actionIndex(){
        $params=Yii::$app->request->get();
        //文件类型,1图片(默认),2 语音,3视频
        $type=isset($params['type'])?$params['type']:1;
        $group_id=isset($params['group_id'])?$params['group_id']:Category::find()->where(['symbol'=>'pic_group','sid'=>Helper::getSid()])->one()->category_id;
        $keyword=isset($params['keyword'])?$params['keyword']:'';
        $provider = new ActiveDataProvider([
            "query"=>Media::find()
                ->select(['media_id','name','url','group_id'])
                ->Where(['type'=>$type])
                ->andFilterWhere(['group_id'=>$group_id])
                ->andFilterWhere(['like','name',$keyword])
                ->orderBy('created_at desc')
        ]);
        // 获取分页和排序数据
        $media = $provider->getModels();
        // 获取所有页面的数据项的总数
        $totalCount = $provider->getTotalCount();
        $data = ['list' => $media,'pagination'=>['total' => $totalCount]];
        return ["message"=>"成功","code"=>1,"data"=>$data];
    }

    /**
     * 修改
     * @param $id
     * @author nwh@caiyoudata.com
     * @time 2018/7/9 18:06
     */
    public function actionUpdate($id){
        $params=Yii::$app->request->post();
        if(isset($params['group_id'])&&empty(Category::find()->where(['category_id'=>$params['group_id']])->all())){
            return ["message"=>"分组id错误,请检查","code"=>10000];
        }
        $media=Media::findOne($id);
        $media->load($params,'');
        if(!$media->save()){
            return ["message"=>"修改失败","code"=>10002,"data"=>$media->errors];
        }else{
            return ["message"=>"修改成功","code"=>1];
        }

    }

    /**
     *  软删除
     * @param $id
     * @author nwh@caiyoudata.com
     * @time dt
     */
    public function actionDelete($id){
        $media=Media::findOne($id);
        if(empty($media)){
            return ["message"=>"图片主键错误","code"=>BaseErrorCode::$OBJECT_NOT_FOUND];
        }else{
            $media->status=0;
            if(!$media->save()){
                return ["message"=>"删除失败","code"=>BaseErrorCode::$SAVE_DB_ERROR,"data"=>$media->errors];
            }else{
                return ["message"=>"删除成功","code"=>1];
            }
        }
    }
    /**
     * 修改图片的分组
     * @author nwh@caiyoudata.com
     * @time 2018/7/16 17:42
     */
    public function actionUpdateCategory(){
        $parmas=Yii::$app->request->post();
        $group_id=$parmas['group_id'];
        $name=$parmas['name'];
        $category=Category::findOne($group_id);
        if($category['name']=="未分组"){
            return ["message"=>"默认分组不可修改","code"=>BaseErrorCode::$PARAMS_ERROR];
        }
        if(empty($category)){
            return ["message"=>"分组id错误,请检查","code"=>BaseErrorCode::$PARAMS_ERROR];
        }
        //只修改名字
        $category->name=$name;
        if(!$category->save()){
            return ["message"=>"修改失败","code"=>BaseErrorCode::$SAVE_DB_ERROR,"data"=>$category->errors];
        }else{
            return ["message"=>"修改成功","code"=>1];
        }
    }

    /**
     * 删除图片分组
     * @param $group_id
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2018/7/16 19:27
     */
    public function actionDeleteCategory($group_id){
        $category=Category::find()->where(['category_id'=>$group_id])->one();
        if(empty($category)){
            return ["message"=>"分组id错误,请检查","code"=>BaseErrorCode::$PARAMS_ERROR];
        }
        if($category['name']=="未分组"){
            return ["message"=>"该分组不可删除","code"=>BaseErrorCode::$PARAMS_ERROR];
        }
        //找到要删除分组的文件
        $medias=Media::find()->where(['group_id'=>$group_id])->andWhere(['sid'=>Helper::getSid()])->all();
        //文件id收集
        $media_id=array_column($medias,'media_id');
        //删除分类表里的文件分组
        $code_c=$category->delete();
        if($code_c===false){
            return ["message"=>"删除失败","code"=>BaseErrorCode::$DELETE_DB_ERROR];
        }
        //修改文件里的该分组到其他分组里
        $code=Media::updateAll(['group_id'=>$this->group_id()],['in','media_id',$media_id]);
        if($code===false){
            return ["message"=>"删除失败","code"=>BaseErrorCode::$DELETE_DB_ERROR
            ];
        }else{
            return ["message"=>"操作成功","code"=>1];
        }
    }
    /**
     * 批量分组
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2018/7/9 18:01
     */
    public function actionBatchSetGroup(){
        $params=Yii::$app->request->post();
        $cat=Category::find()->where(['category_id'=>$params['group_id']])->all();
        if(empty($cat)){
          return ["message"=>"分组id错误,请检查","code"=>10000];
        }
        $code=Media::updateAll(['group_id'=>$params['group_id']],['in','media_id',$params['media_id']]);
        if($code>0){
            return ["message"=>"操作成功",'code'=>1];
        }else{
            return ["message"=>"操作失败",'code'=>10003];
        }
    }
    /**
     * 批量删除
     * @return array
     * @params
     * @author nwh@caiyoudata.com
     * @time 2018/7/9 17:59
     */
    public function actionBatchDelete(){
        $params=Yii::$app->request->post();
        $code=Media::deleteAll(['in','media_id',$params['media_id']]);
        if($code>0){
            return ["message"=>"操作成功",'code'=>1];
        }else{
            return ["message"=>"操作失败",'code'=>BaseErrorCode::$DELETE_DB_ERROR];
        }
    }

    public function group_id(){
        $pic_group=Category::find()->where(['symbol'=>'pic_group'])->one();
        if(empty($pic_group)){
            $pic_group=new Category();
            $data['symbol']='pic_group';
            $data['name']="无分组";
            $data['sid']=Helper::getSid();
            $data['sort']=1;
            $pic_group->load($data,'');
            $pic_group->save();
        }
        return $pic_group->category_id;
    }
}