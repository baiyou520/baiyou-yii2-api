<?php
/**
 * 记录操作日志，基于https://github.com/cakebake/yii2-actionlog 改写
 */
namespace baiyou\common\components;

use baiyou\backend\models\ActionLog;
use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;

/**
 * To use ActionLogBehavior, simply insert the following code to your ActiveRecord class:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *          'actionlog' => [
 *              'class' => 'baiyou\common\components\ActionLogBehavior',
 *          ],
 *     ];
 * }
 * ```
 */

class ActionLogBehavior extends Behavior
{
    /**
    * @var string The message of current action
    */
    public $message = null;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
//            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
        ];
    }

    public function afterInsert($event)
    {
        // 排除日志表自身,没有主键的表不记录
        if($event->sender instanceof \baiyou\backend\models\Actionlog || !$event->sender->primaryKey()) {
            return;
        }
        $this->message = "新增了编号为%s的%s数据";
        $tableName = $event->sender->tableSchema->name;
        $this->message = sprintf($this->message,$event->sender->getPrimaryKey(), $tableName);

        ActionLog::add($this->message,null,0);
    }
    public function beforeUpdate($event)
    {
        // 排除日志表自身,没有主键的表不记录
        if($event->sender instanceof \baiyou\backend\models\Actionlog || !$event->sender->primaryKey()) {
            return;
        }
        $this->message = "修改了编号为%s的%s数据";
        $tableName = $event->sender->tableSchema->name;
        $this->message = sprintf($this->message,$event->sender->getPrimaryKey(), $tableName);
        ActionLog::add($this->message,null,0);
    }

    public function beforeDelete($event)
    {
        // 排除日志表自身,没有主键的表不记录
        if($event->sender instanceof \baiyou\backend\models\Actionlog || !$event->sender->primaryKey()) {
            return;
        }
        $this->message = "删除了编号为%s的%s数据";
        $tableName = $event->sender->tableSchema->name;
        $this->message = sprintf($this->message,$event->sender->getPrimaryKey(), $tableName);
        ActionLog::add($this->message,null,0);
    }

//    public function afterFind($event)
//    {
//        ActionLog::add(ActionLog::LOG_STATUS_ADMIN, $this->message);
//    }
}
