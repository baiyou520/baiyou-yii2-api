<?php

namespace baiyou\backend\models;

use baiyou\common\models\Instance;
use Yii;
/**
 * This is the model class for table "action_log".
 *
 * @property int $action_log_id 主键
 * @property int $sid sid，来自总后台数据库instance表中instance_id
 * @property int $user_id 用户id
 * @property string $user_ip IP
 * @property int $trigger_from 日志来源:0,中台，1，微信
 * @property string $action 方法
 * @property string $controller 控制器
 * @property string $module 操作模块
 * @property int $status 状态:0,给开发人员看，1，给客户看
 * @property string $message 操作内容
 * @property string $detail 详情
 * @property int $created_at 创建时间戳
 * @property int $updated_at 修改时间戳
 *
 * @property Instance $s
 */
class ActionLog extends \baiyou\common\components\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'action_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sid'], 'required'],
            [['sid', 'user_id', 'trigger_from', 'status', 'created_at', 'updated_at'], 'integer'],
            [['message', 'detail'], 'string'],
            [['user_ip'], 'string', 'max' => 15],
            [['action', 'controller'], 'string', 'max' => 100],
            [['module'], 'string', 'max' => 20],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Instance::className(), 'targetAttribute' => ['sid' => 'sid']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'action_log_id' => '主键',
            'sid' => 'sid，来自总后台数据库instance表中instance_id',
            'user_id' => '用户id',
            'user_ip' => 'IP',
            'trigger_from' => '日志来源:0,中台，1，微信',
            'action' => '方法',
            'controller' => '控制器',
            'module' => '操作模块',
            'status' => '状态:0,给开发人员看，1，给客户看',
            'message' => '操作内容',
            'detail' => '详情',
            'created_at' => '创建时间戳',
            'updated_at' => '修改时间戳',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Instance::className(), ['sid' => 'sid']);
    }
    /**
     * Adds a message to ActionLog model
     *
     * @param string $status The log status information
     * @param mixed $message The log message
     * @param string $module  操作模块
     */
    public static function add($message = null, $module = null,$status = 1)
    {
        $model = Yii::createObject(__CLASS__);

        // 判断操作来自微信端，还是中台
        $controllerNamespace = Yii::$app->requestedAction->controller->module->controllerNamespace;
        if (strpos($controllerNamespace,'backend') !== false){
            $model->trigger_from = '0'; // 日志来源:0,中台，1，微信
        }
        if (strpos($controllerNamespace,'frontend') !== false){
            $model->trigger_from = '1'; // 日志来源:0,中台，1，微信
        }
        $model->user_id = Yii::$app->user->id ?? 0;
        $model->user_ip = $_SERVER['REMOTE_ADDR'];
        $model->action = Yii::$app->requestedAction->id;
        $model->controller = Yii::$app->requestedAction->controller->id;
        $model->module = ($module !== null) ? $module : Yii::$app->requestedAction->controller->id;
        $model->status = $status;
        $model->message = ($message !== null) ? $message : null;
        if ($model->save()){
            return true;
        }else{
            \Yii::error($model->errors,'操作日志插入失败！');
            return false;
        }
    }
}
