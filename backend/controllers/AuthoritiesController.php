<?php
/**
 * Created by PhpStorm.
 * User: nwh
 * Date: 2018/7/18
 * Time: 9:30
 */

namespace baiyou\backend\controllers;
use baiyou\common\components\BaseErrorCode;
use yii;
use yii\db\Query;
use yii\data\ActiveDataProvider;

use baiyou\backend\models\AuthItem;
use baiyou\backend\models\AuthItemChild;

class AuthoritiesController extends BaseController
{
    public $modelClass = '';
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
     * 角色列表
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2018/7/18 13:57
     */
    public function actionIndex(){
        //数据
        $roles=AuthItem::find()->select(['name','description'])->where(['type'=>1])->andwhere(['!=','name','super_admin'])->all();
        //总条数
        $total=count($roles);
        $data=["list"=>$roles,'pagination'=>['total' => $total]];
        return ["code"=>1, "message"=>"获取角色列表成功！", "data"=>$data];
    }

    /**
     * 获得某个角色的所有权限
     * @param $id
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2018/7/19 16:24
     */
    public function actionGetPermissionsOfRole(){
        $parmas=Yii::$app->request->get();
        $id=isset($parmas['id'])?$parmas['id']:0;
        //角色验证
        $role_item=AuthItem::find()->select(['name','description'])
            ->where(['name'=>$id,'type'=>1])
            ->andWhere(['not like','name','_admin'])
            ->one();
        if(empty($role_item)){
            return ["message"=>"角色名不正确","code"=>BaseErrorCode::$PARAMS_ERROR];
        }
        //找到角色的权限
        $role_permission=AuthItemChild::find()->where(['parent'=>$id])->andWhere(['not like','child','/'])->all();
        $role_perm=array_column($role_permission,'child');

        //找到所有权限
        $permission=(new Query())->from('auth_item ai')
            ->select(['ai.name','aic.child auth'])
            ->where(['type'=>2])
            ->leftJoin('auth_item_child aic','aic.parent=ai.name')
            ->andWhere(['not like','child','/'])
            ->all();
        $all_permission=$this->genTree($permission,'name','auth');

        //判断哪些是角色已经选择的权限
        $role_permissions=$this->check_permission($all_permission,$role_perm,'auth',false,true);
        $data=array_values($role_permissions);
        return ["message"=>"OK","code"=>1,"data"=>$data];

//        $parmas=Yii::$app->request->get();
//        $name = $parmas['name'] ?? '';
//
//        //角色验证
//        $role_item=AuthItem::find()->select(['name','description'])
//            ->where(['name'=>$name,'type'=>1])
//            ->andwhere(['!=','name','super_admin'])
//            ->one();
//        if(empty($role_item)){
//            return ["message"=>"角色名不正确","code"=>BaseErrorCode::$PARAMS_ERROR];
//        }
//
//        //找到角色的权限
//        $role_permission=AuthItemChild::find()->where(['parent'=>$name])->andWhere(['not like','child','/'])->all();
//        $role_perm=array_column($role_permission,'child');
//        $data['role']=$role_item;
//
//        //找到所有权限
//        $permission=(new Query())->from('auth_item ai')
//            ->select(['ai.name','aic.child auth'])
//            ->where(['type'=>2])
//            ->leftJoin('auth_item_child aic','aic.parent=ai.name')
//            ->andWhere(['not like','child','/'])
//            ->all();
//        $all_permission=$this->genTree($permission,'name','auth');
//
//        //判断哪些是角色已经选择的权限
//        $role_permissions=$this->check_permission($all_permission,$role_perm,'auth',false);
//        $data["permissions"]=array_values($role_permissions);
//        return ["message"=>"OK","code"=>1,"data"=>$data];
        //找到参数中角色权限
    }
    /**
     * 添加
     * @return array|bool
     * @author nwh@caiyoudata.com
     * @time 2018/7/19 17:02
     */
    public function actionCreate(){
        $params=Helpers::trim_tags(Yii::$app->request->post());
        //参数检查
        $check=Helpers::setempty($params,['name','description','rule']);
        if($check['code']!=1){
            return $check;
        }
        //是否有重复角色
        $role=AuthItem::find()->where(['type'=>1])
            ->andWhere(['name'=>$params['name']])
            ->one();
        if(!empty($role)){
            return ["message"=>"角色已存在","code"=>ErrorCode::$PARAMS_CKECK_ERROR];
        }
        $params['type']=1;//类型
        $params['created_at']=time();
        $params['updated_at']=time();
        //添加角色
        $role=new AuthItem();
        if(!$role->load($params,'')||!$role->save()){
            return ["message"=>"角色添加失败","code"=>ErrorCode::$SAVE_DB_ERROR,"data"=>$role->errors];
        }
        //添加权限点
        foreach($params['rule'] as $value){
            $auth_item_child=new AuthItemChild();
            $data['child']=$value;
            $data['parent']=$params['name'];
            if(!$auth_item_child->load($data,'')||!$auth_item_child->save()){
                return ["message"=>"角色添加成功,权限添加失败","code"=>ErrorCode::$SAVE_DB_ERROR,"data"=>$auth_item_child->errors];
            }
        }
        return ["message"=>"添加成功","code"=>1];
    }
    /**
     * 修改
     * @param $id
     * @author nwh@caiyoudata.com
     * @time 2018/7/19 17:02
     */
    public function actionUpdates($id){
        //角色验证
        $role_item=AuthItem::find()
            ->where(['name'=>$id,'type'=>1])
            ->andWhere(['not like','name','_admin'])
            ->one();
        if(empty($role_item)){
            return ["message"=>"角色名不正确","code"=>ErrorCode::$PARAMS_CKECK_ERROR];
        }
        $params=Helpers::trim_tags(Yii::$app->request->post());
        //新的角色名是否冲突
        if(!empty(AuthItem::find()->select(['name','description'])->where(['name'=>$params['name'],'type'=>1])->andWhere(['<>','name',$id])->one())){
            return ["message"=>"角色名重复,请重新写","code"=>ErrorCode::$PARAMS_CKECK_ERROR];
        }
        $role_item->name=$params['name'];
        $role_item->description=$params['description'];
        if(!$role_item->save()){
            return ["message"=>"角色修改失败",'code'=>$role_item->errors];
        }
        //找到原来的权限点删掉
        $code_c=AuthItemChild::deleteAll(['parent'=>$role_item['name']]);
        if($code_c===false){
            return ["message"=>"修改失败","code"=>ErrorCode::$BATCH_DELETE_DB_ERROR];
        }
        //新增刚选的权限点
        foreach($params['rule'] as $value){
            $auth_item_child=new AuthItemChild();
            $data['child']=$value;
            $data['parent']=$role_item['name'];
            if(!$auth_item_child->load($data,'')||!$auth_item_child->save()){
                return ["message"=>"角色添加成功,权限添加失败-".$value,"code"=>ErrorCode::$SAVE_DB_ERROR,"data"=>$auth_item_child->errors];
            }
        }
        return ["message"=>"修改成功","code"=>1];
    }

    /**
     * 角色删除
     * @param $id
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2018/7/19 20:03
     */
    public function actionDeletes($id){
        //判断是否存在该角色
        $role=AuthItem::find()->where(['type'=>1])
            ->andWhere(['name'=>$id])
            ->andWhere(['not like','name','admin'])
            ->one();
        if(empty($role)){
            return ["message"=>"id参数错误","code"=>ErrorCode::$PARAMS_CKECK_ERROR];
        }
        //该角色下的权限都要删掉
        $codes=AuthItemChild::deleteAll(['parent'=>$id]);
        if($codes===false){
            return ["message"=>"删除失败了","code"=>ErrorCode::$BATCH_DELETE_DB_ERROR];
        }
        //删除角色记录
        $code=$role->delete();
        if(!$code) {
            return ["message"=>"删除失败","code"=>ErrorCode::$DELETE_DB_ERROR];
        }else{
            return ["message"=>"删除成功","code"=>1];
        }
    }

    /**
     * 找权限数,配合subset方法使用
     * @param $list
     * @param string $p 父级字段名
     * @param string $s 子级字段名
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2018/7/19 13:46
     */
    private function genTree($list,$p='',$s='') {
        $tree = array();
        $packData = array();
        //子级 集合
        $child=array_column($list,$s);
        //父级 集合
        $parent=array_unique(array_column($list,$p));
        foreach ($list as $val){
            //父级可能会有子级部分内容,找到不在子集上的
            if(!in_array($val[$p],$child)){
                $temp=$val;
                //判断当前子级是否在父级集合中
                if(in_array($val[$s],$parent)){
                    //找到子级的所有下级集合
                    $temp['subset']=$this->subset($list,$child,$parent,$val[$s],$p,$s);
                }
                unset($temp[$p]);
                $tree[$val[$p]][$s]=$val[$p];
                $tree[$val[$p]]['subset'][]=$temp;
//                return $tree;
            }
        }
        return $tree;
    }

    /**
     * 找子级内容
     * @param $list   数组集合
     * @param $child  子级 集合
     * @param $parent 父级 集合
     * @param $son    要查询的子级元素
     * @param $p      父级 字段名
     * @param $s      子级 字段名
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2018/7/19 13:47
     */
    private function subset($list,$child,$parent,$son,$p,$s){
        $data=[];
        foreach ($list as $value){
            //找到父级等于要查找的子级内容
            if($value[$p]==$son){
                $temp=$value;
                //是否还存在下级
                if(in_array($temp[$s],$parent)){
                    $temp['subset']=$this->subset($list,$child,$parent,$son,$p,$s);
                }
                unset($temp[$p]);
                $data[]=$temp;
            }
        }
        return $data;
    }

    /**
     * 对已有权限加true
     * @param $all_permission
     * @param $role_perm
     * @return mixed
     * @author nwh@caiyoudata.com
     * @time 2018/7/19 15:48
     */
    private function check_permission($all_permission,$role_perm,$s,$check=false,$select=false){
        foreach($all_permission as $key => &$value){
            $value['check']=$check;
            if(in_array($value[$s],$role_perm)){
                $value['check']=true;
            }
            if(isset($value['subset'])&&!empty($value['subset'])){
                $value['subset']=$this->check_permission($value['subset'],$role_perm,$s,$value['check'],$select);
            }
            if($value['check']==false&&$select==true&&empty($value['subset'])){
                unset($all_permission[$key]);
            }
            if($select){
                unset($all_permission[$key]['check']);
            }
        }
        return $all_permission;
    }
}