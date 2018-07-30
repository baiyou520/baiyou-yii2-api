<?php
/**
 * Created by PhpStorm.
 * User: nwh
 * Date: 2018/7/18
 * Time: 9:30
 */

namespace baiyou\backend\controllers;
use baiyou\common\components\BaseErrorCode;
use baiyou\common\components\Helper;
use function DeepCopy\deep_copy;
use Prophecy\Doubler\ClassPatch\HhvmExceptionPatch;
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
        $roles=AuthItem::find()->select(['name','description'])->where(['type'=>1])->andwhere(['!=','name','root'])->all();
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
//            ->andWhere(['not like','name','_admin'])
            ->one();
        if(empty($role_item)){
            return ["message"=>"角色名不正确","code"=>BaseErrorCode::$PARAMS_ERROR];
        }

        //找到所有权限

        $results=(new Query())->from('auth_item ai')
            ->select(['ai.name pid' ,'aic.child id' ,'aic.child title'])
            ->where(['type'=>2])
            ->andWhere(['not like ','child','/'])
//            ->andWhere(['= ','name',$id])
//            ->andWhere(['=','name','系统'])
            ->leftJoin('auth_item_child aic','aic.parent=ai.name')
            ->all();
        $all_permissions = $this->generateTree($results);


        //找到角色的权限
        $results=AuthItemChild::find()->where(['parent'=>$id])->andWhere(['not like','child','/'])->all();
        $role_permissions=array_column($results,'child');


//        //找到所有权限
////        $permission=(new Query())->from('auth_item ai')
////            ->select(['ai.name','aic.child auth'])
////            ->where(['type'=>2])
////            ->leftJoin('auth_item_child aic','aic.parent=ai.name')
////            ->andWhere(['not like','child','/'])
////            ->all();
//        $permission=(new Query())->from('auth_item ai')
//            ->select(['ai.name'])
//            ->where(['type'=>2])
////            ->leftJoin('auth_item_child aic','aic.parent=ai.name')
//            ->andWhere(['not like','name','/'])
//            ->all();


//        Helper::p($all_roles);


//        $all_permission=$this->genTree($permission,'name','auth');
//
//        //判断哪些是角色已经选择的权限
        $permissions=$this->check_permission2($all_permissions,$role_permissions,'title',false,true);
//        $data=array_values($role_permissions);
//        Helper::p($permissions);//
        return ["message"=>"OK","code"=>1,"data"=>$permissions];

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

    private function check_permission2(&$all_permission,$role_perm,$s,$check=false,$select=false){
        foreach($all_permission as $key => &$value){

            // 保留原有结构，从根节点开始
            $value['check']=$check;
            if(in_array($value[$s],$role_perm)){
                $value['check']=true;
            }

            // 处理子节点
            if(isset($value['children'])&&!empty($value['children'])){
                $this->check_permission2($value['children'],$role_perm,$s,$value['check'],$select);
            }

            // 去掉没有权限的节点
            if (!$value['check'] && empty($value['children'])){
                unset($all_permission[$key]);
                $all_permission =  array_values($all_permission); // 使用 unset 并未改变数组的原有索引。如果打算重排索引（让索引从0开始，并且连续），可以使用 array_values
            }
        }
        return $all_permission;
    }


    function generateTree($array){
        //第一步 构造数据
        $items = array();
        foreach($array as $value){
            $items[$value['id']] = $value;
        }

        //第二部 遍历数据 生成树状结构
        $tree = array();
        foreach($items as $key => $item){
            if(isset($items[$item['pid']])){
                if (strpos($item['title'],'/') === 0){ // 叶子节点
                    $items[$item['pid']]['isLeaf'] = true;
                }else{
                    $items[$item['pid']]['children'][] = &$items[$key];
                }
            }else{
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }

//    private $tree = [];
    private function getAllPermissionsTree(){
//        // 1.找到所有权限
//        $permissions=(new Query())->from('auth_item ai')
//            ->select(['ai.name'])
//            ->where(['type'=>2])
//            ->andWhere(['not like','name','/'])
//            ->all();
//
//        // 2.找到所有角色
//        $results = AuthItem::find()->where(['type' => 1])->asArray()->all();
//        $all_roles = [];
//        foreach ($results as $role){
//            array_push($all_roles,$role['name']);
//        }
//
//        // 3.找到顶级节点
//        $top_nodes = [];
//        foreach ($permissions as $per){
//            // 得到权限点的所有上级分配，因为一个权限点可能会直接分配给某个角色，所以这里要
//            // 去掉这样的数据，从而明确权限的层级关系，这块用的数组的array_diff相差完成，待完善
//            $results = AuthItem::findOne($per['name'])->authItemChildren0;
//            $father_nodes = [];
//            foreach ($results as $r){
//                array_push($father_nodes,$r['parent']);
//            }
//            $diff = array_diff($father_nodes, $all_roles);
//            if (!$diff){// 相差后不存在上级节点，意味着这个是根节点了
//                array_push($top_nodes,$per['name']);
//            }
//        }

        // 1.找到顶级节点, 创建顶级节点的时候，描述里面要以L0打头
        $top_nodes = AuthItem::find()->where(['like','description','L0%',false ])->asArray()->all();
//
        // 2.找到所有权限点，即分配关系
        $permissions=(new Query())->from('auth_item ai')
            ->select(['ai.name pid' ,'aic.child id' ,'aic.child title'])
            ->where(['type'=>2])
            ->andWhere(['not like ','child','/'])
//            ->andWhere(['=','name','系统'])
            ->leftJoin('auth_item_child aic','aic.parent=ai.name')
            ->all();
//        Helper::p($permissions);
        // 3.根据顶级节点，递归生成权限树
        function list_to_tree($list, $pid)
        {
            $child = array();
            if (!empty($list)) {
                foreach ($list as $k => &$v) {
//                    $v['key'] == $v['title'];
                    if ($v['pid'] == $pid) {
////
//                        if (strpos($v['auth'],'/') === 0){ // 如果已经是叶子节点了，则停止递归
//
//                            $v['isLeaf'] = true;
////
//                        }else{
//                            Helper::p($v);
                        $v['children'] = list_to_tree($list, $v['id']);
                        if (strpos($v['title'],'/') === 0){ // 叶子节点
                            $v['isLeaf'] = true;
                            //                            Helper::p($v);
                        }
                        $child[] = $v;
//                        }

                        //unset($list_to_tree[$k]);
                    }
                }
            }
            return $child;
        }

//        $permissions_tree = list_to_tree($permissions,'权限点');
        $permissions_tree = $this->generateTree($permissions);
//        foreach ($top_nodes as $node){
//            Helper::p($node,false);
//            $permissions_tree = array_merge($permissions_tree,);
//        } Helper::p($permissions_tree,false);die();




//        $leaf_node['title'] = '客户修改';
//        Helper::p( $leaf_node);
//        $this->getTree3($permissions,$leaf_node);
//        Helper::p( $permissions_tree);
//        Helper::p($permissions);
//        $child=array_column($permissions,'auth');
//        //父级 集合
//        $parent=array_unique(array_column($permissions,'name'));
//        Helper::p($parent);
//        // 4.根据顶级节点递归生成tree
//        foreach ($top_nodes as $node){
//
//            $asss = array_search($node['name'],$permissions,true);
//
//        }


        return $permissions_tree;
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
        Helper::p($child);
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
            if(isset($value['children'])&&!empty($value['children'])){
                $value['children']=$this->check_permission($value['children'],$role_perm,$s,$value['check'],$select);
            }
            if($value['check']==false&&$select==true&&empty($value['children'])){
                unset($all_permission[$key]);
            }
            if($select){
                unset($all_permission[$key]['check']);
            }
        }
        return $all_permission;
    }
}