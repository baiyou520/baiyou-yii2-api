<?php

namespace baiyou\backend\models;

use baiyou\common\models\Instance;
use Yii;

/**
 * This is the model class for table "notice".
 *
 * @property int $notice_id 主键
 * @property int $sid sid，来自总后台数据库instance表中instance_id
 * @property string $title 标题
 * @property string $content 内容
 * @property int $type 类型：1:店铺通知,2:订单提醒,3:维权待办,4:提现提醒
 * @property string $related_route 跳转用的相关路由
 * @property int $related_id 跳转到的对应ID
 * @property int $status 是否已读，1:已读，0:未读
 * @property int $created_at 创建时间戳
 * @property int $updated_at 修改时间戳
 *
 * @property Instance $s
 */
class Notice extends \baiyou\common\components\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notice';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content'], 'required'],
            [['sid', 'type', 'related_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 50],
            [['related_route'], 'string', 'max' => 100],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Instance::className(), 'targetAttribute' => ['sid' => 'sid']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'notice_id' => '主键',
            'sid' => 'sid，来自总后台数据库instance表中instance_id',
            'title' => '标题',
            'content' => '内容',
            'type' => '类型：1:店铺通知,2:订单提醒,3:维权待办,4:提现提醒',
            'related_route' => '跳转用的相关路由',
            'related_id' => '跳转到的对应ID',
            'status' => '是否已读，1:已读，0:未读',
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
     * @return \yii\db\ActiveQuery
     */
    public function getNoticeUsers()
    {
        return $this->hasMany(NoticeUser::className(), ['notice_id' => 'notice_id']);
    }

    /**
     * 手动添加通知
     * @param $title
     * @param $content
     * @param $type
     * @param string $related_route
     * @param string $related_id
     * @author sft@caiyoudata.com
     * @time   2018/9/1 上午11:17
     */
    public static function add($title,$content,$type,$related_route='',$related_id=0)
    {
        $model = new Notice();
        $model->title = $title;
        $model->content = $content;
        $model->related_route = $related_route;
        $model->related_id = $related_id;
        $model->type = $type;
        if ($model->save()){

            // 新增通知已读标记记录，效率待完善 todo
            $users = User::findAll(['status' => 10]);
            foreach ($users as $user) {
                $nu = new NoticeUser();
                $nu->user_id = $user->id;
                $nu->notice_id = $model->notice_id;
                if (!$nu->save()) {
                    Yii::error($nu->errors,'通知已读标志插入失败！');
                }
            }
            return true;
        }else{
            Yii::error($model->errors,'通知插入失败！');
            return false;
        }
    }
}
