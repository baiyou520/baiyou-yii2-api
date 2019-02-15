<?php
namespace baiyou\backend\models;

use baiyou\common\models\JwtModel;

/**
 * This is the model class for table "user".
 *
 *
 * @property int $id id，来自总后台数据库
 * @property int $sid sid，来自总后台数据库instance表中instance_id
 * @property string $username 用户名(即百优账号)
 * @property string $name 姓名(昵称)
 * @property string $phone 联系方式(电话)
 * @property string $openid 微信移动端标识符
 * @property int $status 激活状态:10为启用，0位禁用
 * @property int $created_at 创建时间戳
 * @property int $updated_at 修改时间戳
 *
 * @property ActionLog[] $actionLogs
 * @property AuthAssignment[] $authAssignments
 */
class User  extends JwtModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'username', 'name', 'phone'], 'required'],
            [['id', 'sid', 'status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'name'], 'string', 'max' => 30],
            [['phone'], 'string', 'max' => 20],
            [['openid'], 'string', 'max' => 28],
            [['id', 'sid'], 'unique', 'targetAttribute' => ['id', 'sid']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id，来自总后台数据库',
            'sid' => 'sid，来自总后台数据库instance表中instance_id',
            'username' => '用户名(即百优账号)',
            'name' => '姓名(昵称)',
            'phone' => '联系方式(电话)',
            'openid' => '微信移动端标识符',
            'status' => '激活状态:10为启用，0位禁用',
            'created_at' => '创建时间戳',
            'updated_at' => '修改时间戳',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActionLogs()
    {
        return $this->hasMany(ActionLog::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['user_id' => 'id']);
    }

    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information
        unset($fields['sid']);
        $fields_from_other_tables = [
            'role' => function($model) {
                return $model->authAssignments[0]->item_name;
            }
        ];
        return array_merge($fields,$fields_from_other_tables);
    }
}