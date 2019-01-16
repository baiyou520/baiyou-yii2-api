<?php
/**
 * 微信端权限认证控制器
 * User: billyshen
 * Date: 2018/6/1
 * Time: 上午10:56
 */

namespace baiyou\frontend\controllers;

use baiyou\common\components\BaseErrorCode;
use baiyou\common\models\CustomerExt;
use baiyou\common\models\Instance;
use baiyou\common\models\Customer;
use baiyou\common\components\Helper;
use frontend\modules\v1\controllers\InitController;
use yii\rest\ActiveController;
use Yii;
class AuthController extends ActiveController
{
    public $modelClass = '';
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
            ],
        ];

        return $behaviors;
    }
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * 小程序获取用户openID
     * @return array
     * @author  billyshen 2018/6/1 下午5:30
     */
    public function actionWxJsCode2Session(){
        // 获得实例信息
        $sid=Helper::getSid();
        $instance = Instance::findOne($sid);
        $shop_name = $instance->name;
        if ($instance->is_bind === 0){ // 共享版
            $instance = Instance::find()->where(['sid'=>Yii::$app->params['share_sid']])->one();
        }
        $appid = $instance->applet_appid;
        $secret = $instance->applet_appsecret;
        $data = Yii::$app->request->post();
        $jscode=$data['jscode'];
//        $nickname=Yii::$app->request->post('nickname');
//        $avatarUrl=Yii::$app->request->post('avatarUrl');
//        $parentId= Yii::$app->request->post('parent_id') ?? 0;
        $url='https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$jscode.'&grant_type=authorization_code';
//        $out=Helper::https_request($url);
        $out=json_decode($this->wx_https_request($url));
        if(isset($out->errcode) && $out->errcode!=0){
            return ['message'=>'与微信服务器通信失败,请检查appid是否填写正确！','code'=>$out->errcode,'data'=>$out->errmsg];
        }
        $customer=Customer::findOne(['openid'=>$out->openid]);
//        $data['nickname'] = $nickname;
//        $data['name'] = $nickname;
//        $data['avatar'] = $avatarUrl;
//        $data['parent_id'] = $parentId;
        $is_first_register = false; // 是否首次注册
        if(empty($customer)){
            $customer = new Customer();
            $data['username']=Helper::randomString(11);
            $data['openid'] = $out->openid;
            $is_first_register = true;
        }else{
            unset($data['parent_id']);
        }
        $customer->load($data, '');
        if (!$res=$customer->save()) {
            return ["message"=>"参数错误","code"=>10002,"data"=>$customer->errors];
        }else{
            InitController::initData($customer,$is_first_register); // 处理其他应用特有的业务逻辑，比如获得新人优惠券
        }
        //检查是否有用户店铺关系表
        $customer_ext=CustomerExt::find()->where(['customer_id'=>$customer->id,'sid'=>$sid])->one();
        if(empty($customer_ext)){
            $sid=Helper::getSid();
            $customer_ext=new CustomerExt();
            $customer_ext->customer_id=$customer->id;
            $customer_ext->sid=$sid;
            $customer_ext->openid=isset($data['openid'])?$data['openid']:'';
            $customer_ext->parent_id=isset($data['parent_id'])?$data['parent_id']:0;
            if($customer_ext->save()){
                Yii::error(json_encode($customer_ext->errors,JSON_UNESCAPED_UNICODE),'微信登录时,添加用户店铺关系记录失败');
            }
        }

        $customer->generateAccessTokenAfterUpdatingClientInfo(true);
        $result['uid']=$customer->id;
//        $result['access_token']=$customer->access_token;
        $result['shop_name']=$shop_name;
        $result['is_first_register']=$is_first_register;
        $result['sessionKey']=$out->session_key;//用户信息解密参数
        $result['openid']=$out->openid;//用户信息解密参数
        return ["message"=>"认证成功","code"=>1,"data"=>$result];
    }

    /**
     * 解密别名
     * @author sft@caiyoudata.com
     * @time   2019/1/11 11:07 AM
     */
    public function actionDecodeAlias(){
        $data = Yii::$app->request->get();
        $alias=$data['alias'];
        $sid = Helper::unlockTool($alias,Yii::$app->params['lockSecretCode']);
        $cookies = Yii::$app->response->cookies;
        // 在要发送的响应中添加一个新的 cookie，以明确去到了哪个实例
        $cookies->add(new \yii\web\Cookie([
            'name' => 'sid',
            'value' => $sid,
            'domain' => Yii::$app->params['cookies_domain'],
            'httpOnly' => true,
//            'expire' => time() + 10 * 365 * 24 * 60 * 60,
        ]));
        return ["message"=>"进入店铺成功","code"=>1,"data"=>$sid];
    }

    /**
     * 判断手机号登录还是注册
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2019/1/15 11:32
     */
    public function actionCheckPhone(){
        $params=Yii::$app->request->post();
        $phone_preg="/^1(3[0-9]|4[579]|5[0-35-9]|7[0-9]|8[0-9])\d{8}$/";
        if(!isset($params['phone'])||empty($params['phone'])){
            return ['message'=>'请提交手机号','code'=>BaseErrorCode::$FAILED];
        }elseif(!preg_match($phone_preg,$params['phone'])){
            return ['message'=>'手机号码不合法','code'=>BaseErrorCode::$FAILED];
        }
        $customer=Customer::find()->where(['phone'=>$params['phone']])->one();
        if(empty($customer)){
            return ['code'=>BaseErrorCode::$SUCCESS,'message'=>'请注册','data'=>2];
        }else{
            return ['code'=>BaseErrorCode::$SUCCESS,'message'=>'请登录','data'=>1];
        }
    }

    /**
     * 登录
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2019/1/15 11:33
     */
    public function actionLoginByPhone(){
        $params=Yii::$app->request->post();
        $check=$this->check($params);
        if($check['code']!=1){
            return $check;
        }else {
            //密码检查
            $customer=$check['data'];
            $code=\Yii::$app->security->validatePassword((string)$params['password'], $customer['password_hash']);
            if(empty($code)){
                return ['message'=>'密码错误','code'=>BaseErrorCode::$FAILED];
            }
            //检查是否与店铺存在关系id
            $sid=Helper::getSid();
            $customer_ext=CustomerExt::find()->where(['customer_id'=>$customer->id,'sid'=>$sid])->one();
            if(empty($customer_ext)){
                $customer_ext=new CustomerExt();
                $customer_ext->customer_id=$customer->id;
                $customer_ext->sid=$sid;
                $customer_ext->openid=isset($params['openid'])?$params['openid']:'';
                $customer_ext->parent_id=isset($params['parent_id'])?$params['parent_id']:0;
                if($customer_ext->save()){
                    Yii::error(json_encode($customer_ext->errors,JSON_UNESCAPED_UNICODE),'登录时,添加加用户店铺关系记录失败');
                }
            }
            Yii::$app->user->id;
            $customer->generateAccessTokenAfterUpdatingClientInfo(true);
            $result['uid'] = $customer->id;
            return ["message" => "认证成功", "code" => 1, "data" => $result];
        }
    }

    /**
     * 手机web端登录
     * @author sft@caiyoudata.com
     * @time   2019/1/11 11:07 AM
     */
    public function actionLoginByPhones(){
        $params=Yii::$app->request->post();
        $phone=isset($params['phone'])?$params['phone']:'';
        $customer = Customer::find()->where(['phone'=>$phone])->one(); // 暂无密码
        if(empty($customer)){
            return ['message'=>'手机号码错误','code'=>BaseErrorCode::$OBJECT_NOT_FOUND];
        }else {
            $customer->generateAccessTokenAfterUpdatingClientInfo(true);
            $result['uid'] = $customer->id;
            return ["message" => "认证成功", "code" => 1, "data" => $result];
        }
    }
    /**
     * 辅助方法，发起http请求
     * @param $url
     * @param null $data
     * @return mixed
     * @author  billyshen 2018/6/1 下午5:32
     */
    protected function wx_https_request($url, $data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**
     *注册
     * @author nwh@caiyoudata.com
     * @time 2019/1/14 17:31
     */
    public function actionReg(){
        $params=Yii::$app->request->post();
        $check=$this->check($params,false);
        if($check['code']!=1){
            return $check;
        }else{
            $tran=Yii::$app->db->beginTransaction();
            try{
                //客户表
//                $customer=Customer::find()->where(['id'=>Yii::$app->user->id])->one();//暂时不用
                $customer=[];
                if(empty($customer)){
                    $customer=new Customer();
                    $params['nickname'] = (string)$params['phone'];
                }
                $params['username']=(string)$params['phone'];
                $params['phone'] = (string)$params['phone'];
                $params['password']=$customer->setPassword($params['password']);
                $customer->generateAuthKey();
                $customer->load($params, '');

                if (!$customer->save()) {
                    Yii::error(json_encode($customer->errors,JSON_UNESCAPED_UNICODE),'用户注册,用户添加失败');

                    return ['code'=>BaseErrorCode::$SAVE_DB_ERROR,'message'=>'参数错误！','data' =>$customer->errors];
                }
                //客户店铺关系表
                $customer_ext=new CustomerExt();
                $data_ext['customer_id']=$customer->id;
                $data_ext['sid']=Helper::getSid();
                $data_ext['openid']=isset($params['openid'])?$params['openid']:'';
                $data_ext['parent_id']=isset($params['parent_id'])?$params['parent_id']:0;
                $customer_ext->load($data_ext, '');
                if (!$customer_ext->save()) {
                    Yii::error(json_encode($customer_ext->errors,JSON_UNESCAPED_UNICODE),'用户注册,用户店铺关系表添加失败');
                    return ['code'=>BaseErrorCode::$SAVE_DB_ERROR,'message'=>'注册失败','data' =>$customer_ext->errors];
                }
                $tran->commit();
            }catch (yii\db\Exception $e){
                $tran->rollBack();
                return ['code'=>BaseErrorCode::$SAVE_DB_ERROR,'message'=>'注册失败','data' =>$e->errorInfo];

            }
            $customer->generateAccessTokenAfterUpdatingClientInfo(true);
            $result['uid'] = $customer->id;
            return ['message'=>'注册成功','code'=>BaseErrorCode::$SUCCESS,'data'=>$result];
        }
    }

    /**
     * 发送注册验证码
     * @author nwh@caiyoudata.com
     * @time 2019/1/15 13:51
     */
    public function actionSendRegCode(){
        $params=Yii::$app->request->post();
        if(!isset($params['phone'])||empty($params['phone'])){
            return ['message'=>'手机号未提交','code'=>BaseErrorCode::$FAILED];
        }
        //格式验证
        $phone="/^1(3[0-9]|4[579]|5[0-35-9]|7[0-9]|8[0-9])\d{8}$/";
        if(!preg_match($phone,$params['phone'])){
            return ['code'=>BaseErrorCode::$FAILED,'message'=>'手机号码不合法'];
        }
        $cache=Yii::$app->cache;
        $code=''.rand(1000,9999);
        $res=$cache->set((string)$params['phone'],[$code,time()],60*5);
        $res=Helper::send_msg($params['phone'],'您的注册验证码是'.$code.',在5分钟内有效。如非本人操作请忽略本短信。');
        $res=json_decode($res,true);
        if($res['error']!=0){
            return ['message'=>'验证码发送失败','code'=>BaseErrorCode::$FAILED,'data'=>$code];
        }
        return ['message'=>'验证码发送成功','code'=>BaseErrorCode::$FAILED];
    }
    /**
     * 登录/注册验证
     * @param $params
     * @param bool $login
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2019/1/15 13:50
     */
    private function check($params,$login=true){
        //参数检查
        if(!isset($params['phone'])||empty($params['phone'])){
            return ['code'=>BaseErrorCode::$FAILED,'message'=>'请提交手机号'];

        }elseif(!isset($params['password'])||empty($params['password'])){
            return ['code'=>BaseErrorCode::$FAILED,'message'=>'请提交密码'];

        }
        //格式验证
        $phone="/^1(3[0-9]|4[579]|5[0-35-9]|7[0-9]|8[0-9])\d{8}$/";
        if(!preg_match($phone,$params['phone'])){
            return ['code'=>BaseErrorCode::$FAILED,'message'=>'手机号码不合法'];
        }
        $password="/^[a-zA-Z\d_!]{6,20}$/";
        if(!preg_match($password,$params['password'])){
            return ['code'=>BaseErrorCode::$FAILED,'message'=>'密码不合法'];
        }
        $customer=Customer::find()->where(['phone'=>$params['phone']])->one();
        if($login){//登录验证
            if (empty($customer)){
                return ['code'=>BaseErrorCode::$FAILED,'message'=>'手机号码未注册'];
            }
        }else{//注册验证

            if(!empty($customer)){
                return ['code'=>BaseErrorCode::$FAILED,'message'=>'手机号码已注册'];
            }
            //验证码
            if(!isset($params['code'])||empty($params['code'])){
                return ['code'=>BaseErrorCode::$FAILED,'message'=>'请提交验证码'];
            }
            $cache = Yii::$app->cache;
            $code = $cache->get((string)$params['phone'])[0];
            if($code!=$params['code']){
                return ['code'=>BaseErrorCode::$FAILED,'message'=>'验证码错误'];
            }
        }
        return ['code'=>1,'message'=>'参数通过','data'=>$customer];
    }
}