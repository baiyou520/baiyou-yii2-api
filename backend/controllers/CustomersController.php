<?php
/**
 * 微信端用户管理控制器
 * User: billyshen
 * Date: 2018/6/2
 * Time: 上午9:15
 */

namespace baiyou\backend\controllers;

use baiyou\backend\models\Config;
use baiyou\common\components\BaseErrorCode;
use baiyou\common\components\Helper;
use baiyou\common\components\Wechat;
use CURLFile;
use Yii;
use yii\db\Query;
use baiyou\common\components\ActiveDataProvider;

class CustomersController extends BaseController
{
    public $modelClass = 'baiyou\common\models\Customer';

    public function actions()
    {
        $actions = parent::actions();
        // 禁用动作
//        unset($actions['index']);
        unset($actions['delete']);
        unset($actions['create']);
//        unset($actions['view']);
//        unset($actions['update']);
        return $actions;
    }

    /**
     * 客服信息 欢迎语
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/9/14 上午10:31
     */

    public function actionWelcome(){
        if (Yii::$app->request->isGet){
            $config = Config::findOne(['symbol' => 'msg_service_welcome']);
            if (empty($config)){
                return ["code"=>BaseErrorCode::$FAILED,"message"=>"暂未设置欢迎语"];
            } else {
                return ["code"=>1,"message"=>"获得欢迎语成功","data"=>json_decode($config->content)];
            }
        } else {
            $data=Yii::$app->request->post();
            $config = Config::findOne(['symbol' => 'msg_service_welcome']);
            if (empty($config)){
                $config = new Config();
            }
            $message_service_welcome = [
                'welcome_type' => $data['welcome_type'], // 1.图文链接形式;2.纯文字形式
                'text_content' => $data['text_content']
            ];
            $config->content = json_encode($message_service_welcome,JSON_UNESCAPED_UNICODE);
            $config->symbol = 'msg_service_welcome';
            $config->encode = 3;
            if ($config->save()){
                return ["code"=>1,"message"=>"更新欢迎语成功","data"=>$config];
            }else{
                return ["code"=>BaseErrorCode::$FAILED,"message"=>"失败","data"=>$config->errors];
            }
        }
    }


    /**
     * 新增临时素材 用于用户发送客服消息或被动回复用户消息
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/9/14 下午1:38
     */

    public function actionUploadTempMedia(){
        $sid = Helper::getSid();
        $data = Yii::$app->request->post();
        $pic_rename = Helper::hex10to64(Yii::$app->user->id). Helper::hex16to64(uniqid(rand())).".jpg"; // 文件唯一名
        move_uploaded_file($_FILES['file']['tmp_name'],$pic_rename);
        $path = new CURLFile(realpath($pic_rename));
        $path = $path->name;
//        Helper::p($_FILES);
        $result = Wechat::uploadTempMedia($sid,$path,$_FILES['file']['type']);
        if (!isset($result['errcode'])){
            unlink($pic_rename);
            return ["code"=>1,"message"=>"新增临时素材成功","data"=>$result];
        }else{
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"新增临时素材失败","data"=>$result];
        }
    }
    /**
     * 获取微信端用户列表数据
     * @return array
     * @author  billyshen 2018/6/2 上午9:26
     */
//    public function actionIndex()
//    {
//        $query = new Query();
//        $request = Yii::$app->request;
//        $parms = $request->get();
//        $keyword = isset($parms['keyword']) ? $parms['keyword'] : "";//昵称/手机号/邮箱
//        $begin = isset($parms['c_begin']) ? $parms['c_begin'] : "";//查找时间范围开始
//        $end = isset($parms['c_end']) ? $parms['c_end'] : "";//时间范围结束
//        $status = isset($parms['status']) ? $parms['status'] : "";//用户状态
//        $provider = new ActiveDataProvider([
//            'query' =>
//                $query->select(['id', 'nickname', 'name', 'avatar', 'last_login_at', 'last_login_ip', 'status', 'phone'])
//                    ->from('customer')
//                    ->andFilterWhere(['like', 'nickname', $keyword])
//                    ->orFilterWhere(['like', 'name', $keyword])
//                    ->andFilterWhere(['>=', 'user.created_at', $begin])
//                    ->andFilterWhere(['<=', 'user.created_at', $end])
//                    ->orderBy('created_at desc')
//        ]);
//
//        // 获取分页和排序数据
//        $models = $provider->getModels();
//
//        // 在当前页获取数据项的数目
//        $count = $provider->getCount();
//
//        // 获取所有页面的数据项的总数
//        $totalCount = $provider->getTotalCount();
//        $data = ['list' => $models, 'pagination' => ['total' => $totalCount]];
//        return ['message' => '获取客户列表成功', 'code' => 1, 'data' => $data];
//    }
}