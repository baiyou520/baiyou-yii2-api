<?php
/**
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/7/26
 * Time: 下午5:38
 */

namespace baiyou\common\components;


use yii\db\ActiveQueryTrait;
use yii\db\ActiveRelationTrait;
use yii\db\Query;

class ActiveQuery extends \yii\db\ActiveQuery
{
    use ActiveQueryTrait;
    use ActiveRelationTrait;
    /**
     * 复写
     * {@inheritdoc}
     */
    public function prepare($builder)
    {
        // NOTE: because the same ActiveQuery may be used to build different SQL statements
        // (e.g. by ActiveDataProvider, one for count query, the other for row data query,
        // it is important to make sure the same ActiveQuery can be used to build SQL statements
        // multiple times.
        if (!empty($this->joinWith)) {
            $this->buildJoinWith();
            $this->joinWith = null;    // clean it up to avoid issue https://github.com/yiisoft/yii2/issues/2687
        }

        if (empty($this->from)) {
            $this->from = [$this->getPrimaryTableName()];
        }

        if (empty($this->select) && !empty($this->join)) {
            list(, $alias) = $this->getTableNameAndAlias();
            $this->select = ["$alias.*"];
        }

        if ($this->primaryModel === null) {
            // eager loading
            $query = Query::create($this);
        } else {
            // lazy loading of a relation
            $where = $this->where;

            if ($this->via instanceof self) {
                // via junction table
                $viaModels = $this->via->findJunctionRows([$this->primaryModel]);
                $this->filterByModels($viaModels);
            } elseif (is_array($this->via)) {
                // via relation
                /* @var $viaQuery ActiveQuery */
                list($viaName, $viaQuery) = $this->via;
                if ($viaQuery->multiple) {
                    $viaModels = $viaQuery->all();
                    $this->primaryModel->populateRelation($viaName, $viaModels);
                } else {
                    $model = $viaQuery->one();
                    $this->primaryModel->populateRelation($viaName, $model);
                    $viaModels = $model === null ? [] : [$model];
                }
                $this->filterByModels($viaModels);
            } else {
                $this->filterByModels([$this->primaryModel]);
            }

            $query = Query::create($this);
            $this->where = $where;
        }

        if (!empty($this->on)) {
            $query->andWhere($this->on);
        }
        /**
         * 复写区域 sft@caiyoudata.com
         * ——————————————————————————————————————————————————————————————————————————————
         * 绝大部分表需要在新增的时候表明数据是属于哪个实例，故在这里从cookies中得到sid，加入筛选条件
         */
        $condition['sid'] = Helper::getSid();
        return $query->andWhere($condition);

//        return $query;
    }
}