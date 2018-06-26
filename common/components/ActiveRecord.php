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
    
}