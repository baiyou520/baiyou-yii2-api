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
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
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
            TimestampBehavior::className(),
        ];
    }

    /**
     * 复写
     * Inserts an ActiveRecord into DB without considering transaction.
     * @param array $attributes list of attributes that need to be saved. Defaults to `null`,
     * meaning all attributes that are loaded from DB will be saved.
     * @return bool whether the record is inserted successfully.
     */
    protected function insertInternal($attributes = null)
    {
        if (!$this->beforeSave(true)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);


        /**
         * 复写区域 sft@caiyoudata.com
         * ——————————————————————————————————————————————————————————————————————————————
         * 绝大部分表需要在新增的时候表明数据是属于哪个实例，故在这里从cookies中得到sid，插入到数据库
         */
        $values['sid'] = Helper::getSid();


        if (($primaryKeys = static::getDb()->schema->insert(static::tableName(), $values)) === false) {
            return false;
        }
        foreach ($primaryKeys as $name => $value) {
            $id = static::getTableSchema()->columns[$name]->phpTypecast($value);
            $this->setAttribute($name, $id);
            $values[$name] = $id;
        }
//        $this->setAttribute('sid', 1);
        $changedAttributes = array_fill_keys(array_keys($values), null);
        $this->setOldAttributes($values);
        $this->afterSave(true, $changedAttributes);

        return true;
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
         * 绝大部分表需要在新增的时候表明数据是属于哪个实例，故在这里从cookies中得到sid，插入到数据库
         */
        $condition['sid'] = Helper::getSid();

        return $query->andWhere($condition);
    }

    /**
     * 复写
     * {@inheritdoc}
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find()
    {
        /**
         * 复写区域 sft@caiyoudata.com
         * ——————————————————————————————————————————————————————————————————————————————
         * 绝大部分表需要在新增的时候表明数据是属于哪个实例，故在这里从cookies中得到sid，插入到数据库
         */
        \Yii::error('sdfsd','sdfsdf');
        return Yii::createObject(ActiveQuery::className(), [get_called_class()])->andWhere(['sid' => Helper::getSid()]);

//        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }


}