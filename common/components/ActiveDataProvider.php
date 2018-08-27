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
        $tab=explode(' ',($query->from)[0]);
        $table=$tab[count($tab)-1];
        $query->andWhere([$table.'.sid' => Helper::getSid()]);

        $this->setPagination(['pageSizeLimit' => [0, 50]]); // 实现per-page给0的时候，得到全部数据 参考：https://stackoverflow.com/questions/27421798/yii2-how-do-change-pagination-per-page-into-restful-web-service-api

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
        $tab=explode(' ',($query->from)[0]);
        $table=$tab[count($tab)-1];
        $query->andWhere([$table.'.sid' => Helper::getSid()]);

        return (int) $query->limit(-1)->offset(-1)->orderBy([])->count('*', $this->db);
    }
}