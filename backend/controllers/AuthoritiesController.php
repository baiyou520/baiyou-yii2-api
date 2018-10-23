<?php
/**
 * Created by PhpStorm.
 * User: nwh
 * Date: 2018/7/18
 * Time: 9:30
 */

namespace baiyou\backend\controllers;
use baiyou\common\components\BaseErrorCode;
use baiyou\common\components\CreateQueryHelper;
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
    public $modelClass = 'baiyou\backend\models\AuthItem';
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

        $query = CreateQueryHelper::createQuery('baiyou\backend\models\AuthItem');
        $provider = new ActiveDataProvider([
            'query' => $query->andFilterWhere(['=','type',1])
                ->andFilterWhere(['!=','name','root']),
            'pagination' => [
                'pageSize' => 1000
            ],
        ]);

        $data = ['list' => $provider->getModels(),'pagination'=>['total' => $provider->getTotalCount()]];
        return  ['message' => '获取角色列表成功','code' => 1,'data' => $data];

//        //数据
//        $roles_system=AuthItem::find()->select(['name','title'])
//            ->where(['sid'=>0])->andWhere(['type'=>1])->andwhere(['!=','name','root'])->all();
//        $roles_custom=AuthItem::find()->select(['name','title'])
//            ->andWhere(['type'=>1])->andwhere(['!=','name','root'])->all();
//        $roles = array_merge($roles_system,$roles_custom);
        //总条数
//        $total=count($roles);
//        $data=["list"=>$roles,'pagination'=>['total' => $total]];
//        return ["code"=>1, "message"=>"获取角色列表成功！", "data"=>$data];
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
        $view=isset($parmas['view'])?false:true; // 是否仅查看权限点
        //角色验证
        $role_item=AuthItem::findOne(['name'=>$id,'type'=>1]);
        if(empty($role_item)){
            return ["message"=>"角色名不正确","code"=>BaseErrorCode::$PARAMS_ERROR];
        }

        //找到所有权限
        $results=(new Query())->from('auth_item ai')
            ->select(['ai.name pkey' ,'aic.child key' ,'aic.child title'])
            ->where(['type'=>2])
            ->andWhere(['not like ','child','/'])
            ->leftJoin('auth_item_child aic','aic.parent=ai.name')
            ->all();
        $all_permissions = $this->generateTree($results);

        //找到角色的权限
        $results=AuthItemChild::find()->where(['parent'=>$id])->andWhere(['not like','child','/'])->all();
        $role_permissions=array_column($results,'child');

        //判断哪些是角色已经选择的权限
        $this->check_permission($all_permissions,$role_permissions,'title',false,$view);

        $data = [
            'name' => $role_item->name,
            'title' => $role_item->title,
            'description' => $role_item->description,
            'permissions' => $all_permissions
        ];
        return ["message"=>"OK","code"=>1,"data"=>$data];
    }

    private function check_permission(&$all_permission,$role_perm,$s,$check=false,$view=false){

        foreach($all_permission as $key => &$value){
            // 默认全部展开
            $value['expanded']=true;

            // 概况和首页必选 ,当然仅查看情况下，可以不禁用，前端会处理
            if (($value['key'] === '概况' || $value['key'] === '首页') && !$view){
                $value['disabled']=true;
            }else{
                $value['disabled']=false;
            }

            // 保留原有结构，从根节点开始
            $value['checked']=$check;
            if(in_array($value[$s],$role_perm)){
                $value['checked']=true;
            }

            // 处理子节点
            if(isset($value['children'])&&!empty($value['children'])){
                $this->check_permission($value['children'],$role_perm,$s,$value['checked'],$view);
            }

            // 只查询，则去掉没有权限的节点
            if($view){
                if (!$value['checked'] && empty($value['children'])){
                    unset($all_permission[$key]);
                    $all_permission =  array_values($all_permission); // 使用 unset 并未改变数组的原有索引。如果打算重排索引（让索引从0开始，并且连续），可以使用 array_values
                }
            }

        }
        return $all_permission;
    }


    function generateTree($array){
        //第一步 构造数据
        $items = array();
        foreach($array as $value){
            $items[$value['key']] = $value;
        }

        //第二部 遍历数据 生成树状结构
        $tree = array();
        foreach($items as $key => $item){
            if(isset($items[$item['pkey']])){
                if (strpos($item['title'],'/') === 0){ // 叶子节点
                    $items[$item['pkey']]['isLeaf'] = true;
                }else{
                    $items[$item['pkey']]['children'][] = &$items[$key];
                }
            }else{
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }


    /**
     * 添加
     * @return array|bool
     * @author nwh@caiyoudata.com
     * @time 2018/7/19 17:02
     */
    public function actionCreate(){
        $params=Yii::$app->request->post();
//        $request=Yii::$app->request;
//        Helper::p($params['permissions']);
//        是否有重复角色
        $role=AuthItem::find()->where(['name'=>$params['title']])
            ->one();
        if(!empty($role)){
            return ["message"=>"角色已存在，或与权限点重名，请更换名称","code"=>BaseErrorCode::$OBJECT_ALREADY_EXIST];
        }

        $params['type']=1; //类型
        $params['name']=$params['title']; //中英同名
        //添加角色
        $role = new AuthItem();
        if(!$role->load($params,'')||!$role->save()){
            return ["message"=>"角色添加失败","code"=>BaseErrorCode::$SAVE_DB_ERROR,"data"=>$role->errors];
        }

        //添加权限点
        foreach($params['permissions'] as $value){
            $auth_item_child=new AuthItemChild();
            $auth_item_child->child = $value;
            $auth_item_child->parent = $params['name'];
            if(!$auth_item_child->save()){
                return ["message"=>"角色添加成功,权限添加失败","code"=>BaseErrorCode::$SAVE_DB_ERROR,"data"=>$auth_item_child->errors];
            }
        }
        return ["message"=>"添加成功","code"=>1];
    }

    /**
     * 修改
     * @param $name
     * @author nwh@caiyoudata.com
     * @time 2018/7/19 17:02
     */
    public function actionUpdateRole(){

        $params=Yii::$app->request->post();
        //角色验证
        $role_item=AuthItem::findOne(['name'=>$params['name']]);
        if(empty($role_item)){
            return ["message"=>"角色名不正确","code"=>BaseErrorCode::$PARAMS_ERROR];
        }

//        //新的角色中文名是否冲突
//        if(!empty(AuthItem::findOne(['title' => $params['title']]))){
//            return ["message"=>"角色名重复,请重新写","code"=>BaseErrorCode::$PARAMS_ERROR];
//        }

        $role_item->title=$params['title'];
        $role_item->description=$params['description'];
        if(!$role_item->save()){
            return ["message"=>"角色修改失败","code"=>BaseErrorCode::$SAVE_DB_ERROR,'data'=>$role_item->errors];
        }

        //找到原来的权限点删掉
        $code_c=AuthItemChild::deleteAll(['parent'=>$role_item['name']]);
        if($code_c===false){
            return ["message"=>"修改失败","code"=>BaseErrorCode::$SAVE_DB_ERROR];
        }
        //新增刚选的权限点
        foreach($params['permissions'] as $value){
            $auth_item_child=new AuthItemChild();
            $auth_item_child->child = $value;
            $auth_item_child->parent = $params['name'];
            if(!$auth_item_child->save()){
                return ["message"=>"角色添加成功,权限添加失败".$value,"code"=>BaseErrorCode::$SAVE_DB_ERROR,"data"=>$auth_item_child->errors];
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
    public function actionDeleteRole($id){
        //判断该角色下面是否包含员工
        $role=AuthItem::findOne(['name'=>$id]);
        if(count($role->authAssignments) > 0){
            return ["message"=>"该角色下包含员工，请先移除相应员工！","code"=>BaseErrorCode::$SAVE_DB_ERROR];
        }

        //该角色下的权限都要删掉
        $codes=AuthItemChild::deleteAll(['parent'=>$id]);
        if($codes===false){
            return ["message"=>"删除失败了","code"=>BaseErrorCode::$SAVE_DB_ERROR];
        }

        //删除角色记录
        $code=$role->delete();
        if(!$code) {
            return ["message"=>"删除失败","code"=>BaseErrorCode::$SAVE_DB_ERROR];
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

//    /**
//     * 对已有权限加true
//     * @param $all_permission
//     * @param $role_perm
//     * @return mixed
//     * @author nwh@caiyoudata.com
//     * @time 2018/7/19 15:48
//     */
//    private function check_permission($all_permission,$role_perm,$s,$check=false,$select=false){
//        foreach($all_permission as $key => &$value){
//            $value['check']=$check;
//            if(in_array($value[$s],$role_perm)){
//                $value['check']=true;
//            }
//            if(isset($value['children'])&&!empty($value['children'])){
//                $value['children']=$this->check_permission($value['children'],$role_perm,$s,$value['check'],$select);
//            }
//            if($value['check']==false&&$select==true&&empty($value['children'])){
//                unset($all_permission[$key]);
//            }
//            if($select){
//                unset($all_permission[$key]['check']);
//            }
//        }
//        return $all_permission;
//    }
}