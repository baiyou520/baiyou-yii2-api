<?php
/**
 * 百优脚手架，处理sid
 * User: billyshen
 * Date: 2018/6/26
 * Time: 上午11:40
 */

namespace baiyou\common\components;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\caching\CacheInterface;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;
use yii\di\Instance;
use yii\rbac\Item;

class DbManager extends \yii\rbac\DbManager
{
    /**
     * Returns all permissions that are directly assigned to user.
     * @param string|int $userId the user ID (see [[\yii\web\User::id]])
     * @return Permission[] all direct permissions that the user has. The array is indexed by the permission names.
     * @since 2.0.7
     */
    protected function getDirectPermissionsByUser($userId)
    {
        $query = (new Query())->select('b.*')
            ->from(['a' => $this->assignmentTable, 'b' => $this->itemTable])
            ->where('{{a}}.[[item_name]]={{b}}.[[name]]')
            ->andWhere(['a.user_id' => (string) $userId])
            ->andWhere(['b.type' => Item::TYPE_PERMISSION])
            ->andWhere(['a.sid' => Helper::getSid()]); // 复写 sft@caiyoudata.com------添加sid限制条件，实现多租户SAAS

        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }

        return $permissions;
    }

    /**
     * Returns all permissions that the user inherits from the roles assigned to him.
     * @param string|int $userId the user ID (see [[\yii\web\User::id]])
     * @return Permission[] all inherited permissions that the user has. The array is indexed by the permission names.
     * @since 2.0.7
     */
    protected function getInheritedPermissionsByUser($userId)
    {
        $query = (new Query())->select('item_name')
            ->from($this->assignmentTable)
            ->where(['user_id' => (string) $userId])
            ->andWhere(['sid' => Helper::getSid()]);  // 复写 sft@caiyoudata.com------添加sid限制条件，实现多租户SAAS

        $childrenList = $this->getChildrenList();
        $result = [];
        foreach ($query->column($this->db) as $roleName) {
            $this->getChildrenRecursive($roleName, $childrenList, $result);
        }

        if (empty($result)) {
            return [];
        }

        $query = (new Query())->from($this->itemTable)->where([
            'type' => Item::TYPE_PERMISSION,
            'name' => array_keys($result),
        ]);
        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }

        return $permissions;
    }
}