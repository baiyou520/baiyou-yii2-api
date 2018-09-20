<?php
/**
 * 用户接口
 * User: billyshen
 * Date: 2018/5/28
 * Time: 下午3:41
 */

namespace baiyou\backend\controllers;
use backend\modules\v1\controllers\InitController;
use baiyou\backend\models\Config;
use baiyou\backend\models\NoticeUser;
use baiyou\common\components\ActiveDataProvider;
use baiyou\common\components\BaseErrorCode;
use baiyou\common\components\Helper;
use baiyou\common\models\Instance;
use Yii;
use yii\db\Query;
use yii\web\HttpException;
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
        $status=isset($parms['status'])? [$parms['status']]:[0,10];//用户状态
        $role=isset($parms['role'])?$parms['role']:""; //角色


        $provider = new ActiveDataProvider([
            'query' =>
                $query->select(['id','username','phone','user.name','user.created_at','user.updated_at','user.status','aa.item_name as role','ai.title as role_alias'])
                    ->from('user')
                    ->leftJoin("auth_assignment aa","aa.user_id=user.id")
                    ->leftJoin('auth_item ai','ai.name=aa.item_name')
                    ->andWhere(['=','aa.sid',Helper::getSid()]) ////这里由于没有设计外键，必须手动加sid
                    ->andFilterWhere(['like','user.name',$keyword])
                    ->orFilterWhere(['like','user.username',$keyword])
                    ->andFilterWhere(['>=','user.created_at',$begin])
                    ->andFilterWhere(['<=','user.created_at',$end])
                    ->andFilterWhere(['in','user.status',$status])
                    ->andFilterWhere(['aa.item_name'=>$role])
                    ->orderBy('created_at desc')
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
     * @return mixed
     * @author  billyshen 2018/5/30 下午3:35
     */
    public function actionView($id){

        $user = User::findOne($id);

        return $user;
    }
    /**
     * 管理员添加员工
     * @return array
     * @throws HttpException
     * @author  billyshen 2018/5/29 下午1:36
     */
    public function actionCreate(){
        $request=Yii::$app->request;
        $data=$request->post();

        //验证账号是否在百优总后台中已经注册过了
        $results = $this->checkExist($data['username']);
        if ($results['code'] != 1){
            return ["code"=>BaseErrorCode::$OBJECT_NOT_FOUND, "message"=>'该用户还未注册为百优用户，请先让其注册！'];
        }

        // 新增用户表
        $user_id = $results['data']['id']; //百优总后台返回的用户id
        $user =  User::find()->andWhere(['id'=>$user_id])
                    ->andWhere(['in','status',[0,10]])
                    ->one();
        if ($user)
            return ["code"=>BaseErrorCode::$OBJECT_ALREADY_EXIST, "message"=>'该用户已经是员工了！'];

        // 添加曾经删除过的用户
        $userDeleted =  User::find()->andWhere(['id'=>$user_id])
            ->andWhere(['in','status',[20]])
            ->one();
        if ($userDeleted){
            $userDeleted['status'] = 10;
            if ($userDeleted->save()) {
                $assignment=AuthAssignment::findOne(['user_id'=>$user_id]);
                $assignment->item_name=$data['role'];
                if($assignment->save()){
                    $url = Yii::$app->params['admin_url'].'/v1/auth/add-employee';
                    $data_to_admin=[
                        "user_id"=> $user_id,
                        "instance_id"=> Helper::getSid(),
                        "is_owner"=> 0,
                    ];
                    $result = Helper::https_request($url,$data_to_admin);
                    if ($result['code'] === 1){
                        return ["code"=>1,"message"=>"添加员工成功！"];
                    }else{
                        return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"具体应用添加员工成功，但总后台添加失败"];
                    }
                }else{
                    return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"用户表添加成功，权限分配失败","data"=>$assignment->errors];
                }
            }else{
                return ["message"=>"参数错误","code"=>BaseErrorCode::$PARAMS_ERROR,"data"=>$userDeleted->errors];
            }
        }


        // 正常添加
        $user = new User();
        $user->id = $user_id; // 这里的用户表的id不是自增的，而是来自百优总后台返回的用户id
        if ($user->load($data, '') && $user->save()) {
            // 分配权限表
            $assignment = new AuthAssignment();
            $assignment->item_name = $data['role'];
            $assignment->user_id = $user_id;
            if($assignment->save()){
                $url = Yii::$app->params['admin_url'].'/v1/auth/add-employee';
                $data_to_admin=[
                    "user_id"=> $user_id,
                    "instance_id"=> Helper::getSid(),
                    "is_owner"=> 0,
                ];
                $result = Helper::https_request($url,$data_to_admin);
                if ($result['code'] === 1){
                    return ["code"=>1,"message"=>"添加员工成功！"];
                }else{
                    return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"具体应用添加员工成功，但总后台添加失败"];
                }
            }else{
                return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"用户表添加成功，权限分配失败","data"=>$assignment->errors];
            }
        }else{
            return ["message"=>"参数错误","code"=>BaseErrorCode::$PARAMS_ERROR,"data"=>$user->errors];
        }
    }

    /**
     * 调用总后台提供的接口，检查该账号是否已经注册了,只有已经注册了的用户才能添加为员工
     * @param $username
     * @return bool
     * @author sft@caiyoudata.com
     * @time   2018/7/25 下午5:59
     */
    private function checkExist($username){
        $url = Yii::$app->params['admin_url'].'/v1/auth/check-account-exist';
        $data_to_admin=[
            "username"=> $username,
        ];
        return Helper::https_request($url,$data_to_admin);
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
                return ["message"=>"参数错误","code"=>BaseErrorCode::$PARAMS_ERROR,"data"=>$user->errors];
            }
            if(isset($parms['role'])&&!empty($parms['role'])){
                $assignment=AuthAssignment::findOne(['user_id'=>$id]);
                $assignment->item_name=$parms['role'];
                if(!$assignment->save()){
                    return ["message"=>"参数错误","code"=>BaseErrorCode::$PARAMS_ERROR,"data"=>$assignment->errors];
                }

            }
            return ["message"=>"修改用户信息成功","code"=>1];
        }
        return ["message"=>"参数错误","code"=>BaseErrorCode::$PARAMS_ERROR,"data"=>$user->errors];
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
            return ["message"=>"该用户不可删除","code"=>BaseErrorCode::$PARAMS_ERROR];
        }

        // 删除员工
        $model->status = 20; // 软删除
        if (!$model->save()) {
            return ["message"=>"参数错误","code"=>BaseErrorCode::$PARAMS_ERROR,"data" => $model->errors];
        }

        // 去服务器上面删除数据
        $url = Yii::$app->params['admin_url'].'/v1/auth/delete-employee';
        $data_to_admin=[
            "user_id"=> $id,
            "instance_id"=> Helper::getSid(),
        ];
        $result = Helper::https_request($url,$data_to_admin);
        if ($result['code'] === 1){
            return ["code"=>1,"message"=>"删除员工成功！"];
        }else{
            return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"具体应用删除员工成功，但总后台删除失败",'data'=>$result];
        }
    }

    /**
     * 登录以后获得菜单，角色，用户信息等
     * @return array
     * @author  billyshen 2018/5/30 上午10:21
     */
    public function actionStartUp(){
        $sid = Helper::getSid();
        $id = \Yii::$app->user->id;
        $userObj = User::findOne($id);
        // 如果找不到用户信息，意味着当前用户并没有这个实例的权限，一般发生在跨应用访问，让程序跳回总控制台即可
        if (!$userObj){
            throw new \yii\web\HttpException(401, 'sid不对，跳回总控制台.');
        }


        //用户角色
        $role_item = $userObj->authAssignments[0]->itemName;

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
        $user = [
            'user_id' => $userObj->id,
            'username' => $userObj->username,
            'name' => $userObj->name,
            'role' => $role_item->name,
            'role_alias' => $role_item->title,
        ];

        $app = [
            'app_name' => Yii::$app->params['app-name'],
        ];
        $responseData = [
            'menu'=>$menu,
            'user'=>$user,
            'app'=>$app
        ];
        return  ['message' => '获取初始化信息成功','code' => 1,'data' => $responseData];
    }
}