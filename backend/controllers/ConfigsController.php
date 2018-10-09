<?php
/**
 * 设置相关控制器
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/7/18
 * Time: 上午9:50
 */

namespace baiyou\backend\controllers;


use baiyou\backend\models\Config;
use baiyou\backend\models\Experiencer;
use baiyou\common\components\BaseErrorCode;
use baiyou\common\components\Helper;
use baiyou\common\components\Wechat;
use baiyou\common\models\Instance;
use Yii;

class ConfigsController extends BaseController
{
    public $modelClass = 'baiyou\backend\models\Config';

    // 0.未提交审核;1.审核中；2.已发布,且在上架状态；3.已发布，但已经下架了
    const RELEASE_FLAG_INIT = 0;
    const RELEASE_FLAG_PROCESSING = 1;
    const RELEASE_FLAG_RELEASED = 2;
    const RELEASE_FLAG_INVISIBLE = 3;

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['delete']);
        unset($actions['update']);
        return $actions;
    }

    /**
     * 获取某配置内容
     * @return array|null|yii\db\ActiveRecord
     * @author nwh@caiyoudata.com
     * @time 2018/7/9 14:43
     */
    public function actionIndex(){
        $params=Yii::$app->request->get();
        $symbol=isset($params['symbol'])?$params['symbol']:"";
        $configs=Config::findOne(['symbol'=>$params['symbol']]);
        if(!empty($configs)){
            if($configs['encode']==2){
                $configs['content']=json_decode(($configs['content']),true);
            }
            return ["message"=>"OK","code"=>1,"data"=>$configs];
        }else{
            return ["message"=>"请检查参数symbol是否正确","code"=>BaseErrorCode::$PARAMS_ERROR];
        }
    }

    /**
     * 修改与添加
     * @return array
     * @author nwh@caiyoudata.com
     * @time 2018/7/9 16:03
     */
    public function actionCreate(){
        $params=Yii::$app->request->post();
        //同一实例下不能有相同配置
        $config=Config::findOne(['symbol'=>$params['symbol']]);
        //为空在新建
        if(empty($config)){
            $config=new Config();
        }
        //否则修改
        //对数组形式的内容,进行属性转换
        if(is_array($params['content'])){
            $params['encode']=2;
            $params['content']=json_encode($params['content'],JSON_UNESCAPED_UNICODE);
        }
        $config->load($params,'');
        if($config->save()){
            return ["message"=>"成功","code"=>1];
        }else{
            return ["message"=>"失败","code"=>BaseErrorCode::$SAVE_DB_ERROR,"data"=>$config->errors];
        }
    }

    /**
     * 设置小程序秘钥
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/7/23 下午7:42
     */
    public function actionSetAppletSecret(){
        $sid = Helper::getSid();
        $data=Yii::$app->request->post();
        $instance=Instance::findOne($sid);
        if($instance->load($data,'') && $instance->save()){
            if (isset($data['applet_appsecret'])){
                $instance->online_qrcode = Wechat::getWechatQrCode($sid);
                $instance->save(); // 更新小程序码，代码略有冗余

                // 把小程序秘钥回填到总后台
                $sid = Helper::getSid();
                $data=Yii::$app->request->post();
                $url = Yii::$app->params['admin_url'].'/v1/open/setAppSecret/'.$sid;
                $data_to_admin=[
                    "applet_appsecret"=> $data['applet_appsecret'],
                ];
                $results = Helper::https_request($url,$data_to_admin);
                if ($results['code'] !== 1){
                    Yii::error($results['message'],'把小程序秘钥回填到总后台失败');
                }

            }
            $instance->experience_qrcode = Yii::$app->params['admin_url'].'/'.$instance->experience_qrcode; // 体验版二维码，存在总后台的后端
            $instance->online_qrcode = Yii::$app->request->hostInfo.'/'.$instance->online_qrcode; // 上线后二维码,存在具体应用的后端

            return ["code"=>1,"message"=>"设置小程序秘钥成功",'data' => $instance];
        }else{
            return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"参数错误","data"=>$instance->errors];
        }
    }

    /**
     * 设置商家手机号，用于接收系统短信通知，如买家下单付款通知等。
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/9/11 上午11:07
     */
    public function actionSetNoticePhone(){
        $data=Yii::$app->request->post();
        $config=Config::findOne(["symbol" => 'notice_phone']);
        if (empty($config)){
            $config = new Config();
        }
        $config->content = $data['notice_phone'];
        $config->symbol ="notice_phone";
        if($config->save()){
            return ["code"=>1,"message"=>"设置商家手机号成功",'data' => $config];
        } else {
            return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"参数错误","data"=>$config->errors];
        }
    }

    /**
     * 获得微信端设置
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/7/24 上午10:38
     */
    public function actionGetAppletSetting(){
        $sid = Helper::getSid();
        $instance=Instance::findOne($sid)->toArray();
        $instance['experience_qrcode'] = Yii::$app->params['admin_url'].'/'.$instance['experience_qrcode']; // 体验版二维码，存在总后台的后端
        $instance['online_qrcode'] = Yii::$app->request->hostInfo.'/'.$instance['online_qrcode'];// 上线后二维码,存在具体应用的后端
        $config=Config::findOne(["symbol" => 'notice_phone']);
        if (!empty($config)){
            $instance['notice_phone'] = $config->content;
        }
        if($instance){
            return ["code"=>1,"message"=>"获得微信设置成功",'data' => $instance];
        }else{
            return ["code"=>BaseErrorCode::$OBJECT_NOT_FOUND,"message"=>"实例未找到"];
        }
    }

    /**
     * 获得微信端设置
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/7/24 上午10:38
     */
    public function actionGetExpMembers(){
        $exp = Experiencer::findAll([
            'status' =>1,
        ]);
        // 获取所有页面的数据项的总数
        $totalCount = count($exp);
        $data = ['list' => $exp,'pagination'=>['total' => $totalCount]];
        return  ['message' => '获取体验者成功','code' => 1,'data' => $data];
    }

    /**
     * 添加体验者
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/7/24 下午3:56
     */
    public function actionAddExpMember(){
        $sid = Helper::getSid();
        $data=Yii::$app->request->post();
        $url = Yii::$app->params['admin_url'].'/v1/open/setExpMember/'.$sid;
        $data_to_admin=[
            "wechat_id"=> $data['wechat_id'],
            "action"=> 1 //添加体验者
        ];
        $results = Helper::https_request($url,$data_to_admin);
        if ($results['code'] == 1){
            $data['userstr'] = $results['data']['userstr'];
            $data['status'] = 1;
            $exp= new Experiencer();
            if($exp->load($data,'') && $exp->save()){
                return ["code"=>1,"message"=>"添加体验者成功"];
            }else{
                return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"保存失败","data" => $exp->errors];
            }
        }else{
            return ["code"=>BaseErrorCode::$SET_EXPERIENCER_FAILED,"message"=>"添加体验者失败，请核实该用户是否已经是体验者了","data"=>$results];
        }

    }

    /**
     * 解绑体验者
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/7/24 下午7:39
     */
    public function actionUnbindExpMember($id){
        $sid = Helper::getSid();
        $exp = Experiencer::findOne($id);
        $url = Yii::$app->params['admin_url'].'/v1/open/setExpMember/'.$sid;
        $data=[
            "wechat_id"=> $exp->wechat_id,
            "action"=> 2 //解绑体验者
        ];
        $results = Helper::https_request($url,$data);
        if ($results['code'] == 1){
            $exp->status = 0;
            if($exp->save()){
                return ["code"=>1,"message"=>"解绑体验者成功"];
            }else{
                return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"保存失败","data" => $exp->errors];
            }

        }else{
            return ["code"=>BaseErrorCode::$SET_EXPERIENCER_FAILED,"message"=>"解绑体验者失败","data"=>$results];
        }

    }

    /**
     * 上传支付商户p12证书
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/8/2 下午2:30
     */
    public function actionUploadCert(){
        if(empty($_FILES)){
            return ["message"=>"文件未上传","code"=>BaseErrorCode::$PARAMS_ERROR];
        }
        $dir = 'uploads/cert/'.Helper::getSid().'/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $app=Instance::findOne(Helper::getSid());
        if(empty($app)){
            Yii::error("当前用户id:".Yii::$app->user->id."所属实例查不到内容","应用id:".Helper::getSid()."找不到记录");
            return ["message"=>"所属应用不正确,联系管理员查证","code"=>BaseErrorCode::$PARAMS_ERROR];
        }
        if(empty($app['wx_mch_id'])){
            return ["message"=>"商户号id未提交","code"=>BaseErrorCode::$PARAMS_ERROR];
        }elseif(empty($app['wx_mch_key'])){
            return ["message"=>"商户号秘钥未提交","code"=>BaseErrorCode::$PARAMS_ERROR];
        }
        $import_password=$app['wx_mch_id'];
        if(move_uploaded_file($_FILES['cert']['tmp_name'],$dir.$_FILES['cert']['name'])){
            $res['host_info']=Yii::$app->request->hostInfo;
            $res['img_path']='/'. $dir.$_FILES['cert']['name'];

            //导出证书pem格式
            system("openssl pkcs12 -clcerts -nokeys -in ".$dir."apiclient_cert.p12 -out ".$dir."apiclient_cert.pem -passin pass:".$import_password,$res_cert);//已完成
            //导出证书密钥pem格式
            system("openssl pkcs12 -nocerts -in ".$dir."apiclient_cert.p12 -out ".$dir."apiclient_key.pem -nodes -passin pass:".$import_password,$res_key);
            if($res_cert!=0||$res_key!=0){
                return ["message"=>"上传失败了","code"=>BaseErrorCode::$PARAMS_ERROR,"data"=>"文件导出失败"];
            }
            //路径保存到数据表
            $app->ssl_cert_path=$dir."apiclient_cert.pem";
            $app->ssl_key_path=$dir."apiclient_key.pem";
            if(!$app->save()){
                return ["message"=>"保存失败","code"=>BaseErrorCode::$SAVE_DB_ERROR,"data"=>$app->errors];
            }
            return ["message"=>"上传成功","code"=>1];
        }else{
            return ["message"=>"文件上传失败","code"=>BaseErrorCode::$FAILED,"data"=>"错误号 ".$_FILES['cert']['error']."p12证书上传失败"];
        }
    }

    /**
     * 获取小程序的第三方提交代码的页面配置
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/7/24 下午7:39
     */
    public function actionGetPages(){
        $sid = Helper::getSid();
        $url = Yii::$app->params['admin_url'].'/v1/open/getPages/'.$sid;
        $results = Helper::https_request($url);
        if ($results['code'] == 1){
            return ["code"=>1,"message"=>"获取小程序的第三方提交代码的页面配置成功","data"=>$results['data']['page_list']];
        }else{
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"失败","data"=>$results];
        }
    }

    /**
     * 获取授权小程序帐号的可选类目
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/8/10 下午4:59
     */
    public function actionGetCategories(){
        $sid = Helper::getSid();
        $url = Yii::$app->params['admin_url'].'/v1/open/getCategories/'.$sid;
        $results = Helper::https_request($url);


        // 递归listtotree
        function generateTree($array){
            //第一步 构造数据
            $items = array();
            foreach($array as $value){
                $items[$value['value']] = $value;
            }
            //第二部 遍历数据 生成树状结构
            $tree = array();
            foreach($items as $key => $item){

                if(isset($items[$item['parent']])){
                    $items[$item['parent']]['children'][] = &$items[$key];
                }else{
                    $tree[] = &$items[$key];
                }
            }
            return $tree;
        }
        $data = [];
        foreach ($results['data']['category_list'] as $cat){
            $temp1 = [ // 填充根节点
                'first_class' => 'top',
                'label' => $cat['first_class'],
                'parent' => 0,
                'value' => $cat['first_id'],
            ];
            $temp2 = [ // 格式化各个节点，方便前端调用
                'first_class' => $cat['first_class'],
                'label' => $cat['second_class'],
                'parent' => $cat['first_id'],
                'value' => $cat['second_id'],
                'isLeaf' => true,
            ];
            array_push($data,$temp1);
            array_push($data,$temp2);

        }

        $tree = generateTree($data);
        if ($results['code'] == 1){
            return ["code"=>1,"message"=>"获取授权小程序帐号的可选类目成功","data"=>$tree];
        }else{
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"失败","data"=>$results];
        }
    }

    /**
     * 将第三方提交的代码包提交审核
     * @param $id
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/8/10 下午6:48
     */
    public function actionSubmitAudit(){
        $sid = Helper::getSid();
        $data=Yii::$app->request->post();
        $url = Yii::$app->params['admin_url'].'/v1/open/getCategories/'.$sid;
        $categories = Helper::https_request($url);
        $items = array();
        foreach($categories['data']['category_list'] as $value){
            $items[$value['second_id']] = $value;
        }

        // 格式化数据，以满足微信开发平台要求
        $formated_data =[];
        foreach ($data['item_list'] as $item){
            $tags = '';
            foreach ($item['tag'] as $tag) {
                $tags .= $tag.' ';
            }
            $tags = substr($tags,0,strlen($tags)-1);
            $formated_data[] = [
                'address' => $item['address'],
                'tag' => $tags,
                'first_class' => $items[$item['category'][1]]['first_class'],
                'second_class' => $items[$item['category'][1]]['second_class'],
                'first_id' => $items[$item['category'][1]]['first_id'],
                'second_id' => $items[$item['category'][1]]['second_id'],
                'title' => $item['title'],
            ];
        }
        $url = Yii::$app->params['admin_url'].'/v1/open/submitAudit/'.$sid;
        $data=[
            "item_list"=> $formated_data,
        ];
        $submit_audit_data_config = Config::findOne(['symbol' => 'submit_audit_data']);
        if(empty($submit_audit_data_config)){
            $submit_audit_data_config = new Config();
            $submit_audit_data_config->symbol = 'submit_audit_data';
            $submit_audit_data_config->content = json_encode($data,JSON_UNESCAPED_UNICODE);
            $submit_audit_data_config->encode = 2;
            if(!$submit_audit_data_config->save()){
                return ["message"=>"审核已经提交，但保存提交信息失败","code"=>BaseErrorCode::$SAVE_DB_ERROR,"data"=>$submit_audit_data_config->errors];
            };
        }
        $results = Helper::https_request($url,$data);
        if ($results['code'] == 1){

            $url = Yii::$app->params['admin_url'].'/v1/open/getTemplateInfo/'.$sid;
            $tpl_info = Helper::https_request($url);

            $model = Config::findOne(['symbol' => 'version_related']);
            $released_conf = json_decode($model->content);
            $released_conf->released_flag = self::RELEASE_FLAG_PROCESSING;
            $released_conf->tpl_version = $tpl_info['data']['template_id'];
            $model->content = json_encode($released_conf);
            if(!$model->save()){
                return ["message"=>"审核已经提交，但更新提交信息配置文件失败","code"=>BaseErrorCode::$SAVE_DB_ERROR,"data"=>$model->errors];
            };


            $submit_audit_data_config = Config::findOne(['symbol' => 'submit_audit_data']);
            if(empty($submit_audit_data_config)){
                $submit_audit_data_config = new Config();
                $submit_audit_data_config->symbol = 'submit_audit_data';
                $submit_audit_data_config->content = json_encode($data,JSON_UNESCAPED_UNICODE);
                $submit_audit_data_config->encode = 2;
                if(!$submit_audit_data_config->save()){
                    return ["message"=>"审核已经提交，但保存提交信息失败","code"=>BaseErrorCode::$SAVE_DB_ERROR,"data"=>$submit_audit_data_config->errors];
                };
            }
            return ["code"=>1,"message"=>"提交审核成功","data"=>$results];
        }else{
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"失败","data"=>$results];
        }
    }

    /**
     * 将第三方提交的代码包提交审核 (更新，即第二次即以上提交，取第一次提交数据即可)
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/10/9 下午2:13
     */
    public function actionSubmitUpgrade(){
//        $sid = Helper::getSid();
//        $data=Yii::$app->request->post();
//        $url = Yii::$app->params['admin_url'].'/v1/open/getCategories/'.$sid;
//        $categories = Helper::https_request($url);
//        $items = array();
//        foreach($categories['data']['category_list'] as $value){
//            $items[$value['second_id']] = $value;
//        }
//
//        // 格式化数据，以满足微信开发平台要求
//        $formated_data =[];
//        foreach ($data['item_list'] as $item){
//            $tags = '';
//            foreach ($item['tag'] as $tag) {
//                $tags .= $tag.' ';
//            }
//            $tags = substr($tags,0,strlen($tags)-1);
//            $formated_data[] = [
//                'address' => $item['address'],
//                'tag' => $tags,
//                'first_class' => $items[$item['category'][1]]['first_class'],
//                'second_class' => $items[$item['category'][1]]['second_class'],
//                'first_id' => $items[$item['category'][1]]['first_id'],
//                'second_id' => $items[$item['category'][1]]['second_id'],
//                'title' => $item['title'],
//            ];
//        }
//        $url = Yii::$app->params['admin_url'].'/v1/open/submitAudit/'.$sid;
//        $data=[
//            "item_list"=> $formated_data,
//        ];
//        $results = Helper::https_request($url,$data);
//        if ($results['code'] == 1){
//
//            $url = Yii::$app->params['admin_url'].'/v1/open/getTemplateInfo/'.$sid;
//            $tpl_info = Helper::https_request($url);
//
//            $model = Config::findOne(['symbol' => 'version_related']);
//            $released_conf = json_decode($model->content);
//            $released_conf->released_flag = self::RELEASE_FLAG_PROCESSING;
//            $released_conf->tpl_version = $tpl_info['data']['template_id'];
//            $model->content = json_encode($released_conf);
//            if(!$model->save()){
//                return ["message"=>"审核已经提交，但更新提交信息配置文件失败","code"=>BaseErrorCode::$SAVE_DB_ERROR,"data"=>$model->errors];
//            };
//            return ["code"=>1,"message"=>"提交审核成功","data"=>$results];
//        }else{
//            return ["code"=>BaseErrorCode::$FAILED,"message"=>"失败","data"=>$results];
//        }
    }

    /**
     * 查询最新一次提交的审核状态
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/8/13 下午1:47
     */

    public function actionGetLatestAuditStatus(){
        $model = Config::findOne(['symbol' => 'version_related']);
        $released_conf = json_decode($model->content);
        if($released_conf->released_flag === self::RELEASE_FLAG_INIT){ // 0.未提交审核;1.审核中；2.已发布
            $instance=Instance::findOne(Helper::getSid());
            $data['status'] = -1;
            $data['instance'] = $instance;
            return ["code"=>1,"message"=>"未提交审核","data"=>$data];
        }

        if($released_conf->released_flag === self::RELEASE_FLAG_PROCESSING){ // 审核中，查询具体审核状态
            $sid = Helper::getSid();
            $url = Yii::$app->params['admin_url'].'/v1/open/getLatestAuditStatus/'.$sid;
            $results = Helper::https_request($url);
            if ($results['code'] == 1){
                return ["code"=>1,"message"=>"查询版本状态成功","data"=>$results['data']];
            }else{
                return ["code"=>BaseErrorCode::$FAILED,"message"=>"失败","data"=>$results];
            }
        }

        if($released_conf->released_flag === self::RELEASE_FLAG_RELEASED || $released_conf->released_flag === self::RELEASE_FLAG_INVISIBLE){ // 已经成功发布过了，检查是否新版本可以升级
            $sid = Helper::getSid();
            $url = Yii::$app->params['admin_url'].'/v1/open/getTemplateInfo/'.$sid;
            $results = Helper::https_request($url);
            if ($results['code'] == 1){
//                Helper::p($released_conf['tpl_version']);
                if ($results['data']['template_id'] > $released_conf->tpl_version){
                    $data['status'] = 10;
                    $data['template_id'] = $results['data']['template_id'];
                    $data['user_desc'] = $results['data']['user_desc'];
                    return ["code"=>1,"message"=>"有新的版本","data"=>$data];
                }else{
                    if ($released_conf->released_flag === self::RELEASE_FLAG_RELEASED){
                        $data['status'] = 11;
                    }else{
                        $data['status'] = 12;
                    }

                    return ["code"=>1,"message"=>"当前版本已经是最新版本了","data"=>$data];
                }
            }else{
//            if (strpos($results['data'],'no valid audit_id exist hint') !== false){
//                return ["code"=>BaseErrorCode::$NEVER_SUBMIT_AUDIT,"message"=>"从未提交审核","data"=>$results];
//            }else{
                return ["code"=>BaseErrorCode::$FAILED,"message"=>"失败","data"=>$results];
//            }
            }
        }


    }

    /**
     * 发布已通过审核的小程序
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/8/13 下午4:45
     */
    public function actionRelease(){
        $sid = Helper::getSid();
        $url = Yii::$app->params['admin_url'].'/v1/open/release/'.$sid;
        $results = Helper::https_request($url);
        if ($results['code'] == 1){
            $model = Config::findOne(['symbol' => 'version_related']);
            $released_conf = json_decode($model->content);
            $released_conf->released_flag = self::RELEASE_FLAG_RELEASED;
            $model->content = json_encode($released_conf);
            if(!$model->save()){
                return ["message"=>"小程序已经发布成功，但更新提交信息配置文件失败","code"=>BaseErrorCode::$SAVE_DB_ERROR,"data"=>$model->errors];
            };
            return ["code"=>1,"message"=>"发布已通过审核的小程序成功","data"=>$results['data']];
        }else{
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"失败","data"=>$results];
        }
    }

    /**
     * 修改小程序线上代码的可见状态
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/8/16 下午4:55
     */
    public function actionChangeVisitStatus(){
        $sid = Helper::getSid();
        $model = Config::findOne(['symbol' => 'version_related']);
        $released_conf = json_decode($model->content);
        if ($released_conf->released_flag === self::RELEASE_FLAG_RELEASED){
            $action = 'close';
        }else{
            $action = 'open';
        }

        $url = Yii::$app->params['admin_url'].'/v1/open/changeVisitStatus/'.$sid.'?action='.$action;
        $results = Helper::https_request($url);
        if ($results['code'] == 1){
            if ($released_conf->released_flag === self::RELEASE_FLAG_RELEASED){
                $released_conf->released_flag = self::RELEASE_FLAG_INVISIBLE;
                $data['status'] = 12;
            }else{
                $released_conf->released_flag = self::RELEASE_FLAG_RELEASED;
                $data['status'] = 11;
            }

            $model->content = json_encode($released_conf);
            if(!$model->save()){
                return ["message"=>"修改小程序线上代码的可见状态成功，但更新提交信息配置文件失败","code"=>BaseErrorCode::$SAVE_DB_ERROR,"data"=>$model->errors];
            };
            return ["code"=>1,"message"=>"修改小程序线上代码的可见状态成功","data"=>$data];
        }else{
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"失败","data"=>$results];
        }
    }


    /**
     * 获取指定小程序码，用于店铺推广，商品推广等
     * @author sft@caiyoudata.com
     * @time   2018/9/4 上午11:16
     */
    public function actionGetWechatQrCodeUnlimited(){
        $sid = Helper::getSid();
        $data = Yii::$app->request->post();
        $qr = Wechat::getWechatQrCodeUnlimited($sid,$data['page'],$data['scene']);
//        return 'data:image/jpeg;base64,'.base64_encode($result); // 以base64格式返回
        if ($qr !== ''){
            return ["code"=>1,"message"=>"获取指定小程序码成功","data"=>'data:image/jpeg;base64,'.base64_encode($qr)];
        }else{
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"获取指定小程序码失败"];
        }
    }

    /**
     * 更新模板
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/7/24 下午7:39
     */
    public function actionUpgrade(){
        $sid = Helper::getSid();
        $url = Yii::$app->params['admin_url'].'/v1/open/upgrade/'.$sid;
        $results = Helper::https_request($url);
        if ($results['code'] == 1){
            $instance = Instance::findOne($sid);
            $experience_qrcode = Yii::$app->params['admin_url'].'/'.$instance->experience_qrcode; // 体验版二维码，存在总后台的后端
            return ["code"=>1,"message"=>"更新模板成功","data"=>$experience_qrcode];
        }else{
            return ["code"=>BaseErrorCode::$FAILED,"message"=>"失败","data"=>$results];
        }
    }

    /**
     * 激活模板消息
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/9/20 上午9:42
     */
    public function actionActivateTpl(){
        $sid = Helper::getSid();
        $data=Yii::$app->request->post();
        $config=Config::findOne(["symbol" => 'template_message']);
        $tpls = json_decode($config->content,true);

        foreach ($tpls as &$tpl) {
            if ($tpl['at_id'] === $data['at_id']){
                $results = Wechat::addTemplateMessage($sid,$data['at_id'], json_decode($tpl['keywords']));
                if($results['errcode'] === 0){
                    $tpl['tpl_id'] = $results['template_id'];
                    $config->content = json_encode($tpls,JSON_UNESCAPED_UNICODE);
                    $config->save();
                    return ["code"=>1,"message"=>"激活模板消息成功",'data' => $results];
                } else {
                    return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"激活模板消息失败"];
                }
            }
        }

        return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"激活模板消息失败,未找到模板id"];

//        $items = array();
//        foreach($tpls as $value){
//            $items[$value['at_id']] = $value;
//        }
//        $tpl = $items[$data['at_id']];

    }

    /**
     * 获取模板库某个模板标题下关键词库
     * @return array
     * @author sft@caiyoudata.com
     * @time   2018/9/20 上午9:42
     */
    public function actionGetTplKeywordsIds(){
        $sid = Helper::getSid();
        $data=Yii::$app->request->post();
        $results = Wechat::getTplKeywordsId($sid,$data['at_id']);
        if($results['errcode'] === 0){
            return ["code"=>1,"message"=>"激活模板消息成功",'data' => $results];
        } else {
            return ["code"=>BaseErrorCode::$PARAMS_ERROR,"message"=>"激活模板消息失败"];
        }
    }

//
//    /**
//     * 获取指定小程序码，测试
//     * @author sft@caiyoudata.com
//     * @time   2018/9/4 上午11:16
//     */
//    public function actionGetWechatCodeLimited(){
//        $sid = Helper::getSid();
//        $data = Yii::$app->request->post();
//        $qr = Wechat::getWechatCodeLimited($sid,$data['path']);
//        $qr = base64_encode($qr);
//        if ($qr !== ''){
//            return ["code"=>1,"message"=>"获取指定小程序码成功","data"=>$qr];
//        }else{
//            return ["code"=>BaseErrorCode::$FAILED,"message"=>"获取指定小程序码失败"];
//        }
//    }
}