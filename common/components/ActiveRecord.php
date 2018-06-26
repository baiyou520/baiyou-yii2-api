<?php
/**
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/6/26
 * Time: 下午2:27
 */

namespace baiyou\common\components;


class ActiveRecord extends \yii\db\ActiveRecord
{
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
     * @see update()
     * @param array $attributes attributes to update
     * @return int|false the number of rows affected, or false if [[beforeSave()]] stops the updating process.
     * @throws StaleObjectException
     */
    protected function updateInternal($attributes = null)
    {
        if (!$this->beforeSave(false)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);

        /**
         * 复写区域 sft@caiyoudata.com
         * ——————————————————————————————————————————————————————————————————————————————
         * 绝大部分表需要在新增的时候表明数据是属于哪个实例，故在这里从cookies中得到sid，插入到数据库
         */
        $values['sid'] = Helper::getSid();


        if (empty($values)) {
            $this->afterSave(false, $values);
            return 0;
        }
        $condition = $this->getOldPrimaryKey(true);
        $lock = $this->optimisticLock();
        if ($lock !== null) {
            $values[$lock] = $this->$lock + 1;
            $condition[$lock] = $this->$lock;
        }
        // We do not check the return value of updateAll() because it's possible
        // that the UPDATE statement doesn't change anything and thus returns 0.
        $rows = static::updateAll($values, $condition);

        if ($lock !== null && !$rows) {
            throw new StaleObjectException('The object being updated is outdated.');
        }

        if (isset($values[$lock])) {
            $this->$lock = $values[$lock];
        }

        $changedAttributes = [];
        foreach ($values as $name => $value) {
            $changedAttributes[$name] = isset($this->_oldAttributes[$name]) ? $this->_oldAttributes[$name] : null;
            $this->_oldAttributes[$name] = $value;
        }
        $this->afterSave(false, $changedAttributes);

        return $rows;
    }
}