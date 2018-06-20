<?php
/**
 * 用户接口
 * User: billyshen
 * Date: 2018/5/28
 * Time: 下午3:41
 */

namespace baiyou\backend\controllers;
use mdm\admin\models\form\ChangePassword;
use Yii;
use yii\db\Query;
use yii\web\HttpException;
use yii\data\ActiveDataProvider;
use baiyou\backend\models\User;
use baiyou\backend\models\AuthAssignment;
use baiyou\backend\models\AuthItem;
use mdm\admin\components\MenuHelper;

class UsersController extends BaseController
{
    public $modelClass = 'baiyou\backend\models\User';

    public function actions()
    {
        $actions = parent::actions();

        // 禁用动作
        unset($actions['index']);
        unset($actions['delete']);
        unset($actions['create']);
        unset($actions['view']);
        unset($actions['update']);
        return $actions;
    }

    /**
     * 获取用户列表数据
     * @return array
     * @author  billyshen 2018/5/28 下午8:24
     */
    public function actionIndex(){
        $query = new Query();
        $request=Yii::$app->request;
        $parms=$request->get();
        $keyword=isset($parms['keyword'])?$parms['keyword']:"";//昵称/手机号/邮箱
        $begin=isset($parms['c_begin'])?$parms['c_begin']:"";//查找时间范围开始
        $end=isset($parms['c_end'])?$parms['c_end']:"";//时间范围结束
        $status=isset($parms['status'])?$parms['status']:"";//用户状态
        $role=isset($parms['role'])?$parms['role']:""; //角色

        $provider = new ActiveDataProvider([
            'query' =>
                $query->select(['id','username','user.name','avatar_thumb','email','user.created_at','user.updated_at','last_login_at','last_login_ip','user.status','aa.item_name as role','ai.description as role_alias'])
                    ->from('user')
                    ->leftJoin("auth_assignment aa","aa.user_id=user.id")
                    ->leftJoin('auth_item ai','ai.name=aa.item_name')
                    ->andFilterWhere(['like','user.name',$keyword])
                    ->orFilterWhere(['like','user.email',$keyword])
                    ->orFilterWhere(['like','user.username',$keyword])
                    ->andFilterWhere(['>=','user.created_at',$begin])
                    ->andFilterWhere(['<=','user.created_at',$end])
                    ->andFilterWhere(['user.status'=>$status])
                    ->andFilterWhere(['aa.item_name'=>$role])
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
        $data = ['list' => $models,'pagination'=>['total' => $totalCount]];
        return  ['message' => '获取用户列表成功','code' => 1,'data' => $data];
    }

    /**
     * 详情添加角色
     * @param $id
     * @return array|bool
     * @author  billyshen 2018/5/30 下午3:35
     */
    public function actionView($id){
        $user=(new Query())->from("user u")
            ->select(["u.*","aa.item_name role","ai.description role_alias"])
            ->leftJoin("auth_assignment aa","aa.user_id=u.id")
            ->leftJoin("auth_item ai","ai.name=aa.item_name")
            ->where(["u.id"=>$id])
            ->one();
        return $user;
    }
    /**
     * 管理员创建角色
     * @return array
     * @throws HttpException
     * @author  billyshen 2018/5/29 下午1:36
     */
    public function actionCreate(){
        $request=Yii::$app->request;
        if ($request->isPost){
            $data=$request->post();
            //验证注册数据合法性
            $res=$this->validate($data,true);
            if ($res['code']!=200){
                return ["message"=>$res['message'],"code"=>$res['code'],'data' => []];
            }
            $user = new User();
            $data['password']=$user->setPassword($data['password']);
            $user->load($data, '');
            $user->generateAuthKey();
            if (!$user->validate($data)||!$res=$user->save()) {
                return ["message"=>"参数错误","code"=>10002,"data"=>$user->errors];
            }
            $id=$user->id;
            $assignment=new AuthAssignment();
            $assignment->item_name=empty($data['role'])?"user":$data['role'];
            $assignment->user_id=$id;
            $assignment->created_at=time();
            $code=$assignment->save();
            if(!$code){
                return ["message"=>"用户加色加载失败,请手动修改","code"=>10002];
            }
            return ["message"=>"注册成功","code"=>1];
        }
    }

    /**
     * 用户修改
     * @param $id
     * @return array
     * @throws HttpException
     * @author  billyshen 2018/5/30 下午3:35
     */
    public function actionUpdate($id){
        $parms=Yii::$app->request->post();
        $user=User::findOne($id);
        if($user->load($parms,'')){
            if(!$user->save()){
                return ["message"=>"参数错误","code"=>10002,"data"=>$user->errors];
            }
            if(isset($parms['role'])&&!empty($parms['role'])){
                $assignment=AuthAssignment::find()->where(['user_id'=>$id])->one();
                if(empty($assignment)){
                    $assignment=new AuthAssignment();
                    $assignment->user_id=$id;
                    $assignment->created_at=time();
                }
                $assignment->item_name=$parms['role'];
                if(!$assignment->save()){
                    return ["message"=>"参数错误","code"=>10002,"data"=>$assignment->errors];
                }

            }
            return ["message"=>"修改用户信息成功","code"=>1];
        }
        return ["message"=>"参数错误","code"=>10002,"data"=>$user->errors];
    }

    /**
     * 用户删除
     * @param $id
     * @return array
     * @throws HttpException
     * @author  billyshen 2018/5/30 下午3:36
     */
    public function actionDelete($id){
        $model = User::findOne($id);
        if($model['username']=="sadmin"){
            return ["message"=>"该用户不可删除","code"=>10008];
        }

        $code=AuthAssignment::find()->where(['user_id'=>$id])->one()->delete();
        if (!$code) {
            return ["message"=>"角色表信息未删除","code"=>10003];
        }

        $code=$model->delete();
        if (!$code) {
            return ["message"=>"参数错误","code"=>10002,"data" => $model->errors];
        }

        return ["message"=>"删除成功","code"=>1];
    }
    /**
     * 密码/账号验证
     * @param $data
     * @param bool $type
     * @return array
     * @author  billyshen 2018/5/29 下午1:38
     */
    private function validate($data,$type=false){

        //格式验证
        $username="/^(1(3[0-9]|4[579]|5[0-35-9]|7[0-9]|8[0-9])\d{8})|([\x7f-\xff\a-zA-Z]{2,30})$/";
        if(!preg_match($username,$data['username'])){
            return ['code'=>10006,'message'=>'请输入正确格式作为账号！'];
        }
        $password="/^[a-zA-Z\d_]{6,20}$/";
        if(!preg_match($password,$data['password'])){
            return ['code'=>10007,'message'=>'请输入英文、数字、下划线6-20位字符密码！'];
        }
        if($data['password']!==$data['repassword']){
            return ['code'=>10004,'message'=>'两次密码输入不一致！'];
        }
        if($type){//注册时的用户名重复验证,密码忘记重置时不用
            //验证用户名不重复
            $validate=User::find()->where(['username'=>$data['username']])->one();
            if ($validate){
                return ['message'=>'账号已存在！','code'=>10009];
            }
        }
        return ['code'=>200];
    }

    /**
     * 登录以后获得菜单，角色，用户信息等
     * @return array
     * @author  billyshen 2018/5/30 上午10:21
     */
    public function actionStartUp(){
        $id = \Yii::$app->user->id;
        $query=New Query();
        //用户角色
        $item_name=$query->select('aa.item_name as role,ai.description as role_alias')
            ->from('auth_assignment aa')
            ->leftJoin('auth_item ai','ai.name=aa.item_name')
            ->where("aa.user_id=$id")->one()
        ;
        //菜单
        $menu="";
        $callback = function($menu){
            $data = json_decode($menu['data'], true);
            $items = $menu['children'];
            $return = [];
            //处理我们的配置
            if ($data) {
                //icon
                isset($data['icon'])  && $return['icon'] = $data['icon'];
                //link
                isset($data['link']) && $return['link'] = $data['link'];
                //text
                isset($data['text']) && $return['text'] = $data['text'];
                //group
                isset($data['group']) && $return['group'] = $data['group'];

            }

            $items && $return['children'] = $items;
            return $return;
        };
        $menu = MenuHelper::getAssignedMenu($id,null,$callback,true);
        $userObj = User::findOne($id);
        $user = [
            'user_id' => $userObj->id,
            'username' => $userObj->username,
            'name' => $userObj->name,
            'avatar' => $userObj->avatar_thumb,
            'email' => $userObj->email,
            'role' => $item_name['role'],
            'role_alias' => $item_name['role_alias'],
        ];
        $app = [
            'name' => '百优数据',
            'description' => '百优数据脚手架'
        ];
        $responseData = [
            'menu'=>$menu,
            'user'=>$user,
            'app'=>$app
        ];
        return  ['message' => '获取初始化信息成功','code' => 1,'data' => $responseData];
    }

    /**
     * 角色列表,辅助用于用户列表筛选
     * @return array|\yii\db\ActiveRecord[]
     * @author  billyshen 2018/5/30 上午10:26
     */
    public function actionRoles(){
        $role=AuthItem::find()->select(['name','description'])->where(['type'=>1])->all();
        return ['message' => '获取角色信息成功','code' => 1,'data' => $role];
    }

    /**
     * 修改密码
     * @return array
     * @author  billyshen 2018/5/30 下午4:53
     */
    public function actionChangePassword(){
        $model = new ChangePassword();
        $request = Yii::$app->request;
        $data = $request->post();
        if($request->isPost) {
            $datas=$data['ChangePassword'];
            if($datas['newPassword']!=$datas['retypePassword']){
                return ["message"=>"两次密码输入不一致","code"=>10004];
            }
            if (!$model->load($data)||!$model->change()) {
                return ["message"=>"原密码不正确","code"=>10005];
            }
            return ["message"=>"密码修改成功","code"=>1];
        }
    }

    /**
     * 修改头像
     * @return array
     * @throws HttpException
     * @author  billyshen 2018/5/30 下午5:16
     */
    public function actionEditAvatar(){
        $request=Yii::$app->request;
        $parms=$request->post();
        if(!isset($parms['user_id'])||empty($parms['user_id'])||empty($user=User::findOne($parms['user_id']))){
            return ["message"=>"id参数错误","code"=>10010];
        }
        if(!empty($parms['avatar'])) {//修改头像
            $user->avatar = $parms['avatar'];
            $user->avatar_thumb = str_replace('avatar', 'avatar_thumb', $parms['avatar']);
            $code=$user->save();
            if(!$code){
                return ["message"=>"参数错误","code"=>10002,"data" => $user->errors];
            }
            return ["message"=>"修改成功","code"=>1];
        }
    }
}