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
use baiyou\common\models\Customer;
use common\models\MyMemberCard;
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
        unset($actions['index']);
        unset($actions['delete']);
        unset($actions['create']);
//        unset($actions['view']);
//        unset($actions['update']);
        return $actions;
    }

    /**
     * "微信客户列表"
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2018/12/19 17:47
     */
    public function actionIndex(){
        $params=Yii::$app->request->get();
        $name=isset($params['name'])?$params['name']:'';//姓名
        $c_begin=isset($params['c_begin'])?$params['c_begin']:'';//注册开始时间
        $c_begin=strlen($c_begin) === 13 ? $c_begin/1000 : $c_begin;
        $c_begin=$c_begin>0?strtotime(date('Y-m-d',(int)$c_begin)):$c_begin;

        $c_end=isset($params['c_end'])?$params['c_end']:'';//注册结束时间
        $c_end=strlen($c_end) === 13 ? $c_end/1000 : $c_end;
        $c_end=$c_end>0?strtotime(date('Y-m-d 23:59:59',(int)$c_end)):$c_end;
        
        $is_buy=isset($params['is_buy'])?$params['is_buy']:'';//是否购买过,订单完成且没有退款
        $buy=[];//已购买的所有用户,用于where查询
        $buy_ids=[];//已购买的所有用户
        $res=Yii::$app->getDb()->createCommand("SHOW TABLES LIKE 'order'")->queryAll();//因为是通用文件,防止部分项目没有order表
        if(!empty($res)){
            $order_ids=(new Query())->from('order')->select(['user_id'])
                ->where(['order_status'=>4])
                ->andWhere(['<>','is_refund',1])//不在申请退款中
                ->andWhere(['sid'=>Helper::getSid()])
                ->groupBy('user_id')
                ->all();
            $buy_ids=array_unique(array_column($order_ids,'user_id'));
            if($is_buy==1){
                $buy=$buy_ids;
            }
        }
        $member_card_id=isset($params['member_card_id'])?$params['member_card_id']:'';//会员卡拥有查询
        //暂时不能联表查询
        $card_c_ids=[];
        if(!empty($member_card_id)){
            $card_members=MyMemberCard::find()->select(['user_id'])->andWhere(['member_card_id'=>$member_card_id])->andWhere(['<>','status',0])->all();
            $card_c_ids=array_unique(array_column($card_members,'user_id'));
        }
        $model=new ActiveDataProvider([
            'query'=>(new Query())->from('customer_ext ce')
                ->innerJoin('customer c','ce.customer_id=c.id')//用户信息
                ->leftJoin('customer c2','c2.id = ce.parent_id')//用户推荐人信息
                ->select(['c.*','ce.*','c2.name parent_name'])
                ->where(['ce.sid'=>Helper::getSid()])
                ->andFilterWhere(['like','c.nickname',$name])
                ->andFilterWhere(['>=','ce.created_at',$c_begin])
                ->andFilterWhere(['<=','ce.created_at',$c_end])
                ->andFilterWhere(['in','c.id',$buy]) //购买过的
                ->andFilterWhere(['in','c.id',$card_c_ids])//有会员卡的
                ->orderBy('c.id desc')
        ]);
        $list=$model->getModels();
        foreach($list as &$value){
            if(in_array($value['id'],$buy_ids)){
                $value['is_buy']=1;
            }else{
                $value['is_buy']=0;
            }
            $value['created_at']=$value['created_at']*1000;
            $value['updated_at']=$value['updated_at']*1000;
        }
        $data['list']=$list;
        $data['pagination']=['total'=>$model->getTotalCount()];
        return ['message'=>'OK','code'=>BaseErrorCode::$SUCCESS,'data'=>$data];
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
            if ($data['welcome_type'] === 'link'){
                $message_service_welcome = [
                    'welcome_type' => $data['welcome_type'], // 1.图文链接形式;2.纯文字形式
                    'link_picture' => $data['link_picture'],
                    'link_url' => $data['link_url'],
                    'link_title' => $data['link_title'],
                    'link_desc' => $data['link_desc'],
//                    'picture_url_on_own_sever' => $data['picture_url_on_own_sever']
                ];
            }else{
                $message_service_welcome = [
                    'welcome_type' => $data['welcome_type'], // 1.图文链接形式;2.纯文字形式
                    'text_content' => $data['text_content']
                ];
            }

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
        $pic_rename = Helper::hex10to64(Yii::$app->user->id). Helper::hex16to64(uniqid(rand())).".jpg"; // 文件唯一名
        move_uploaded_file($_FILES['file']['tmp_name'],$pic_rename); // 先上传到自己的服务器
        $path = new CURLFile(realpath($pic_rename));
        $result = Wechat::uploadTempMedia($sid,$path); // 再上传到微信服务器
        if (!isset($result['errcode'])){
            $result['url'] = Yii::$app->request->hostInfo.'/'.$pic_rename;
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
    /**
     * " 我的"页面设置
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2018/12/19 15:09
     */
    public function actionCustomerCenter(){
        $request=Yii::$app->request;
        $configs=Config::find()->andWhere(['symbol'=>'customer_center'])->asArray()->one();
        if(!empty($configs)) {
            $configs['content'] = json_decode(($configs['content']), true);
        }
        if($request->isGet){
            if(empty($configs)){
                return ['message'=>'暂未设置','code'=>BaseErrorCode::$FAILED];
            }else{
                return ['message'=>'OK','code'=>BaseErrorCode::$SUCCESS,'data'=>$configs['content']];
            }
        }elseif($request->isPost){
            $params=$request->post();
            //参数检查
            if(!isset($params['menu'])||empty($params['menu'])){
                return ['message'=>'参数不对','code'=>BaseErrorCode::$FAILED,'data'=>'参数menu未设置或为空了'];
            }
            $menu=$params['menu'];
            if(!isset($menu['is_show'])||empty($menu['is_show'])){
                return ['message'=>'菜单必须选择','code'=>BaseErrorCode::$FAILED,'data'=>'菜单里面的参数is_show未设置或为空了'];

            }elseif($menu['is_show']!=1){
                return ['message'=>'菜单必须选择','code'=>BaseErrorCode::$FAILED,'data'=>'参数is_show必须等于1'];
            }
            $detail=$menu['detail'];
            foreach($detail as $value){
                if($value['name']=='distribution'&&(!isset($value['message'])||empty($value['message']))){
                    return ['message'=>'请为分销赚钱添加一个信息','code'=>BaseErrorCode::$FAILED];
                }elseif(mb_strlen($value['title'])>6){
                    return ['message'=>'菜单名称字数不能超过6个字','code'=>BaseErrorCode::$FAILED];
                }
            }
            $config = Config::find()->where(['symbol' => 'customer_center', 'sid' => Helper::getSid()])->one();
            if (empty($config)) {
                $config = new Config();
                $config->symbol = 'customer_center';
                $config->encode = 2;
            }
            $config->content=json_encode($params,JSON_UNESCAPED_UNICODE);
            if(!$config->save()){
                return ['message'=>'保存失败','code'=>BaseErrorCode::$SAVE_DB_ERROR,'data'=>$config->errors];
            }
            return ['message'=>'保存成功','code'=>BaseErrorCode::$SUCCESS];
        }else{
            return ['message'=>'请求错误','code'=>BaseErrorCode::$FAILED];
        }
    }
}