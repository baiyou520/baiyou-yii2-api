<?php
/**
 * 权限认证控制器，处理跟登录，注册相关的功能，此控制器不需要基础BaseController
 * User: billyshen
 * Date: 2018/5/28
 * Time: 下午4:00
 */

namespace baiyou\backend\controllers;
use yii\rest\ActiveController;
use common\models\LoginForm;
use yii\web\HttpException;
use yii;
use yii\db\Query;
use mdm\admin\components\MenuHelper;

class AuthController extends ActiveController
{
    public $modelClass = 'common\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
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

        // 禁用动作
//        unset($actions['index']);
        return $actions;
    }
    //用户登录
    public function actionLogin(){
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post(),'')){
            if ($model->login()){
                $user = $model->getUser();
                $last_login_at= $user['last_login_at'];
                $last_login_ip= $user['last_login_ip'];
                $user->generateAccessTokenAfterUpdatingClientInfo(true);
                $id = implode(',', array_values($user->getPrimaryKey(true)));
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
                    $return = [
                        'text' => $menu['name'],
                        'link' => str_replace("v1/","",$menu['route'])
                    ];
                    //处理我们的配置
                    if ($data) {
                        //visible
                        isset($data['visible']) && $return['visible'] = $data['visible'];
                        //icon
                        isset($data['icon']) && $data['icon'] && $return['icon'] = $data['icon'];
                        //other attribute e.g. class...
                        $return['options'] = $data;
                    }
                    //没配置图标的显示默认图标
                    (!isset($return['icon']) || !$return['icon']) && $return['icon'] = 'icon-list';
                    $items && $return['children'] = $items;
                    return $return;
                };
                $menu = MenuHelper::getAssignedMenu($id,null,$callback,true);
                $responseData = [
                    'user_id'    =>  $id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'avatar' => $user->avatar_thumb,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'last_login_at' => $last_login_at,
                    'last_login_ip' => $last_login_ip,
                    'access_token' => $user->access_token,
                    'role' => $item_name['role'],
                    'role_alias' => $item_name['role_alias'],
                    'menu'=>$menu
                ];
                return ['message' => '登录成功','code' => 1,'data' => $responseData];
            }
            else {
                return ["message"=>"用户名或密码错误","code"=>10002];
            }
        }else {
            return ["message"=>"检查参数是否正确","code"=>10000,'data' => $model->errors];
        }
    }

    //options 登录辅助
    public function actionOptions($id = null) {
        return "ok";
    }
}