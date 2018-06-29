<?php
/**
 * Created by PhpStorm.
 * User: billyshen
 * Date: 2018/6/26
 * Time: 下午4:02
 */

namespace baiyou\common\components;
use yii\base\InvalidConfigException;
use yii\db\QueryInterface;


class ActiveDataProvider extends \yii\data\ActiveDataProvider
{
    /**
     * 复写
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;

        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            if ($pagination->totalCount === 0) {
                return [];
            }
            $query->limit($pagination->getLimit())->offset($pagination->getOffset());
        }
        if (($sort = $this->getSort()) !== false) {
            $query->addOrderBy($sort->getOrders());
        }

        /**
         * 复写区域 sft@caiyoudata.com
         * ————————————————————————————
         *  添加sid限制条件，实现多租户SAAS
         */
        $query->andWhere([($query->from)[0].'.sid' => Helper::getSid()]);

        return $query->all($this->db);
    }

    /**
     * 复写
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;


        /**
         * 复写区域 sft@caiyoudata.com
         * ————————————————————————————
         *  添加sid限制条件，实现多租户SAAS
         */
        $query->andWhere([($query->from)[0].'.sid' => Helper::getSid()]);

        return (int) $query->limit(-1)->offset(-1)->orderBy([])->count('*', $this->db);
    }
}