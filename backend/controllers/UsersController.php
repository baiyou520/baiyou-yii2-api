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
        $status=isset($parms['status'])?$parms['status']:"";//用户状态
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
                    ->andFilterWhere(['user.status'=>$status])
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
        $user =  User::findOne(['id' => $user_id]);
        if ($user)
            return ["code"=>BaseErrorCode::$OBJECT_ALREADY_EXIST, "message"=>'该用户已经是员工了！'];
        else{
            $user = new User();
            $user->id = $user_id; // 这里的用户表的id不是自增的，而是来自百优总后台返回的用户id
        }

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
                $assignment=AuthAssignment::find()->where(['user_id'=>$id])->one();
                if(empty($assignment)){
                    $assignment=new AuthAssignment();
                    $assignment->user_id=$id;
                    $assignment->created_at=time();
                }
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

        $code=AuthAssignment::findOne(['user_id'=>$id])->delete();
        if (!$code) {
            return ["message"=>"角色表信息未删除","code"=>BaseErrorCode::$PARAMS_ERROR];
        }

        $code=$model->delete();
        if (!$code) {
            return ["message"=>"参数错误","code"=>BaseErrorCode::$PARAMS_ERROR,"data" => $model->errors];
        }

        // 还差一个去服务器上面删除数据
        return ["message"=>"删除成功","code"=>1];
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

        // 判断店铺有没有初始化
        $init_config = Config::findOne(['symbol' => 'init']);
        if (empty($init_config)){
            InitController::init();
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


//        $instance = Instance::findOne($sid);
//        $sub_title = '';
//        $license = '';
//        $expired_at = '';
//        switch ($instance->status)
//        {
//            case 0:
//                $sub_title = '欢迎使用'.Yii::$app->params['app-name'].',您的店铺为试用版，为不影响使用，请及时购买正式版！';
//                $license = '试用版';
//                $expired_at =  (int)(($instance->expired_at - time()) / 86400)  .'天后过期，请及时续费！';
//                break;
//            case 1:
//                $sub_title = '欢迎回来，祝您生意欣荣！';
//                $license = '正式版';
//                $expired_at =  (int)(($instance->expired_at - time()) / 86400) .'天后过期，请及时续费！';
//                break;
//            case -1:
//                $sub_title = '您的店铺已经打烊，您仍旧可进行部分操作，但客户无法交易，请及时续费！';
//                $license = '已打烊';
//                $expired_at = '已打烊，请及时续费！';
//                break;
//            default:
//                break;
//        }
//        $qr_code_status = 0; // 出现立即绑定按钮
//        if ($instance->is_bind == 1){
//            $qr_code_status = 1; // 出现设置秘钥按钮
//        }
//        if ($instance->online_qrcode !== ''){
//            $qr_code_status = 2; // 出现2个二维码
//        }
        $app = [
            'app_name' => Yii::$app->params['app-name'],
//            'sid' => $instance->sid,
//            'instance_name' => $instance->name,
//            'status' => $instance->status,
//            'license' => $license, // 版本
//            'expired_at' => $expired_at, // 多久过期
//            'sub_title' => $sub_title,
//            'instance_thumb' => $instance->thumb,
//            'experience_qrcode' => Yii::$app->params['admin_url'].'/'.$instance->experience_qrcode, // 体验版二维码，存在总后台的后端
//            'online_qrcode' => Yii::$app->request->hostInfo.'/'.$instance->online_qrcode,// 上线后二维码,存在具体应用的后端
//            'qr_code_status' => $qr_code_status,// 如何显示微信小程序二维码区域
//            'level' => $instance->level, // 购买版本，暂定
        ];
        $responseData = [
            'menu'=>$menu,
            'user'=>$user,
            'app'=>$app
        ];
        return  ['message' => '获取初始化信息成功','code' => 1,'data' => $responseData];
    }

//    /**
//     * 角色列表,辅助用于用户列表筛选
//     * @return array|\yii\db\ActiveRecord[]
//     * @author  billyshen 2018/5/30 上午10:26
//     */
//    public function actionRoles(){
//        $role=AuthItem::find()->select(['name','description'])->where(['type'=>1])->all();
//        return ['message' => '获取角色信息成功','code' => 1,'data' => $role];
//    }

//    /**
//     * 批量启用/禁用
//     * @return array
//     * @throws HttpException
//     * @author  billyshen 2018/6/21 下午2:33
//     */
//    public function actionSetStatus(){
//        $request=Yii::$app->request;
//        $parms=$request->post();
//        foreach($parms['id'] as $val){
//            $user=User::findOne($val);
//            $item_name=AuthAssignment::find()->where(['user_id'=>$val])->one()['item_name'];
//            if($item_name=="super_admin"&&$parms['status'] == 0){ //super_admin为默认超级管理员角色名
//                return ['message'=>'超管不能禁用','code'=>10001];
//            }
//            $user->status=$parms['status'];
//            $code=$user->save();
//            if(!$code){
//                return ['message'=>'参数错误','code'=>10002,"data" => $user->errors];
//            }
//        }
//        $msg = $parms['status']===0 ? '禁用成功' : '启用成功';
//        return ['message'=>$msg,'code'=>1];
//    }
}