<?php
/**
 * 微信端权限认证控制器
 * User: billyshen
 * Date: 2018/6/1
 * Time: 上午10:56
 */

namespace frontend\modules\v1\controllers;

use backend\modules\v1\models\Instance;
use backend\modules\v1\models\Customer;
use common\frame\GlobalFunctions;
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
        //从数据库获取appid 和 appsecret
        $id=Yii::$app->request->get('id');
        $instance = Instance::findOne($id);
        $appid = $instance['applet_appid'];
        $secret = $instance['applet_appsecret'];

        $jscode=Yii::$app->request->get('jscode');
        $nickname=Yii::$app->request->get('nickname');
        $avatarUrl=Yii::$app->request->get('avatarUrl');
        $url='https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$jscode.'&grant_type=authorization_code';
        $out=json_decode($this->wx_https_request($url));
        if(isset($out->errcode) && $out->errcode!=0){
            return ['message'=>'与微信服务器通信失败,请坚持appid是否填写正确！','code'=>$out->errcode,'data'=>$out->errmsg];
        }
        $customer=Customer::find()->where(['openid'=>$out->openid])->one();
        $data['nickname'] = $nickname;
        $data['name'] = $nickname;
        $data['avatar'] = $avatarUrl;
        if(empty($customer)){
            $customer = new Customer();
            $data['username']=GlobalFunctions::randomString(11);
            $data['openid'] = $out->openid;
            $customer->load($data, '');
        }
        if (!$res=$customer->save()) {
            return ["message"=>"参数错误","code"=>10002,"data"=>$customer->errors];
        }

        $customer->generateAccessTokenAfterUpdatingClientInfo(true);
        $result['uid']=$customer->id;
        $result['access_token']=$customer->access_token;
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