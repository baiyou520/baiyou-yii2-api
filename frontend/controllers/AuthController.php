<?php
/**
 * 微信端权限认证控制器
 * User: billyshen
 * Date: 2018/6/1
 * Time: 上午10:56
 */

namespace baiyou\frontend\controllers;

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
                'Access-Control-Request-Headers' => ['*']
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

        $customer->generateAccessTokenAfterUpdatingClientInfo(true);
        $result['uid']=$customer->id;
        $result['access_token']=$customer->access_token;
        $result['shop_name']=$shop_name;
        $result['is_first_register']=$is_first_register;
        return ["message"=>"认证成功","code"=>1,"data"=>$result];
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
}