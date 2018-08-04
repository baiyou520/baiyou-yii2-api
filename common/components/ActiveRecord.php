<?php
/**
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/6/26
 * Time: 下午2:27
 */

namespace baiyou\common\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQueryInterface;
use yii\helpers\ArrayHelper;

class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                /**
                 * TimestampBehavior：
                 * 创建的时候，默认插入当前时间戳给created_at和updated_at字段
                 * 更新的时候，默认更新当前时间戳给updated_at字段
                 */
                'class'              => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => time(),
            ],
            [
                /**
                 * AttributeBehavior：
                 * 绝大部分表需要在新增的时候表明数据是属于哪个实例，故在这里从cookies中得到sid，插入到数据库
                 */
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_VALIDATE => 'sid',
                ],
                'value' => function ($event) {
                    return Helper::getSid();
                },
            ],
            [
                /**
                 * AttributeBehavior：
                 * 由于返回乘以了1000，修改的时候又不会复写crated_at，而前端又可能会传过来,故在此再除以1000
                 */
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'created_at',
                ],
                'value' => function ($event) {
                    return $this->created_at / 1000;
                },
            ],
            [
                /**
                 * AttributeBehavior：
                 * 由于前端框架处理10位时间戳比较麻烦，故在此乘以1000
                 */
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_AFTER_FIND => 'created_at',
                ],
                'value' => function ($event) {
                    return $this->created_at * 1000;
                },
            ],
            [
                /**
                 * AttributeBehavior：
                 * 由于前端框架处理10位时间戳比较麻烦，故在此乘以1000
                 */
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_AFTER_FIND => 'updated_at',
                ],
                'value' => function ($event) {
                    return $this->updated_at * 1000;
                },
            ],
            [
                /**
                 * ActionLogBehavior：
                 * 操作日志
                 */
                'class' => 'baiyou\common\components\ActionLogBehavior',
            ],

        ];
    }


    /**
     * 复写
     * Finds ActiveRecord instance(s) by the given condition.
     * This method is internally called by [[findOne()]] and [[findAll()]].
     * @param mixed $condition please refer to [[findOne()]] for the explanation of this parameter
     * @return ActiveQueryInterface the newly created [[ActiveQueryInterface|ActiveQuery]] instance.
     * @throws InvalidConfigException if there is no primary key defined.
     * @internal
     */
    protected static function findByCondition($condition)
    {
        $query = static::find();

        if (!ArrayHelper::isAssociative($condition)) {
            // query by primary key
            $primaryKey = static::primaryKey();
            if (isset($primaryKey[0])) {
                $pk = $primaryKey[0];
                if (!empty($query->join) || !empty($query->joinWith)) {
                    $pk = static::tableName() . '.' . $pk;
                }
                // if condition is scalar, search for a single primary key, if it is array, search for multiple primary key values
                $condition = [$pk => is_array($condition) ? array_values($condition) : $condition];
            } else {
                throw new InvalidConfigException('"' . get_called_class() . '" must have a primary key.');
            }
        } elseif (is_array($condition)) {
            $condition = static::filterCondition($condition);
        }

        /**
         * 复写区域 sft@caiyoudata.com
         * ——————————————————————————————————————————————————————————————————————————————
         * 绝大部分表需要在新增的时候表明数据是属于哪个实例，故在这里从cookies中得到sid，加入筛选条件
         */
        $condition['sid'] = Helper::getSid();

        return $query->andWhere($condition);
    }

    /**
     * 复写 暂时不启用
     * {@inheritdoc}
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find()
    {
        /**
         * 复写区域 sft@caiyoudata.com
         * ——————————————————————————————————————————————————————————————————————————————
         * 绝大部分表需要在新增的时候表明数据是属于哪个实例，故在这里从cookies中得到sid，加入筛选条件，
         * 但外部调用后面不能用where，而要用andWhere,即find()->andWhere 否则这里的筛选条件会失效，待完善
         */

        return parent::find()->where(['=', 'sid', Helper::getSid()]);
    }


}