<?php
/**
 * 百优脚手架，处理sid
 * User: billyshen
 * Date: 2018/6/26
 * Time: 上午11:40
 */

namespace baiyou\common\components;
use mdm\admin\models\Assignment;
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
     * {@inheritdoc}
     */
    public function getAssignments($userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return [];
        }

        $query = (new Query())
            ->from($this->assignmentTable)
            ->where(['user_id' => (string) $userId])
            ->andWhere(['sid' => Helper::getSid()]); // 复写 sft@caiyoudata.com------添加sid限制条件，实现多租户SAAS

        $assignments = [];
        foreach ($query->all($this->db) as $row) {
            $assignments[$row['item_name']] = new Assignment([
                'userId' => $row['user_id'],
                'roleName' => $row['item_name'],
                'createdAt' => $row['created_at'],
            ]);
        }

        return $assignments;
    }

    /**
     * {@inheritdoc}
     */
    protected function getItem($name)
    {
        if (empty($name)) {
            return null;
        }

        if (!empty($this->items[$name])) {
            return $this->items[$name];
        }

        $row = (new Query())->from($this->itemTable)
            ->where(['name' => $name])
            ->andWhere(['in', 'sid', [Helper::getSid(),0]]) // 复写 sft@caiyoudata.com------添加sid限制条件，实现多租户SAAS
            ->one($this->db);

        if ($row === false) {
            return null;
        }

        return $this->populateItem($row);
    }


//    /**
//     * {@inheritdoc}
//     * The roles returned by this method include the roles assigned via [[$defaultRoles]].
//     */
//    public function getRolesByUser($userId)
//    {
//        if ($this->isEmptyUserId($userId)) {
//            return [];
//        }
//
//        $query = (new Query())->select('b.*')
//            ->from(['a' => $this->assignmentTable, 'b' => $this->itemTable])
//            ->where('{{a}}.[[item_name]]={{b}}.[[name]]')
//            ->andWhere(['a.user_id' => (string) $userId])
//            ->andWhere(['b.type' => Item::TYPE_ROLE])
//            ->andWhere(['a.sid' => Helper::getSid()]); // 复写 sft@caiyoudata.com------添加sid限制条件，实现多租户SAAS
//
//        $roles = $this->getDefaultRoleInstances();
//        foreach ($query->all($this->db) as $row) {
//            $roles[$row['name']] = $this->populateItem($row);
//        }
//
//        return $roles;
//    }
//
    /**
     * Check whether $userId is empty.
     * @param mixed $userId
     * @return bool
     */
    private function isEmptyUserId($userId)
    {
        return !isset($userId) || $userId === '';
    }
//
//    /**
//     * Returns all permissions that are directly assigned to user.
//     * @param string|int $userId the user ID (see [[\yii\web\User::id]])
//     * @return Permission[] all direct permissions that the user has. The array is indexed by the permission names.
//     * @since 2.0.7
//     */
//    protected function getDirectPermissionsByUser($userId)
//    {
//        $query = (new Query())->select('b.*')
//            ->from(['a' => $this->assignmentTable, 'b' => $this->itemTable])
//            ->where('{{a}}.[[item_name]]={{b}}.[[name]]')
//            ->andWhere(['a.user_id' => (string) $userId])
//            ->andWhere(['b.type' => Item::TYPE_PERMISSION])
//            ->andWhere(['a.sid' => Helper::getSid()]); // 复写 sft@caiyoudata.com------添加sid限制条件，实现多租户SAAS
//
//        $permissions = [];
//        foreach ($query->all($this->db) as $row) {
//            $permissions[$row['name']] = $this->populateItem($row);
//        }
//
//        return $permissions;
//    }
//
//    /**
//     * Returns all permissions that the user inherits from the roles assigned to him.
//     * @param string|int $userId the user ID (see [[\yii\web\User::id]])
//     * @return Permission[] all inherited permissions that the user has. The array is indexed by the permission names.
//     * @since 2.0.7
//     */
//    protected function getInheritedPermissionsByUser($userId)
//    {
//        $query = (new Query())->select('item_name')
//            ->from($this->assignmentTable)
//            ->where(['user_id' => (string) $userId])
//            ->andWhere(['sid' => Helper::getSid()]);  // 复写 sft@caiyoudata.com------添加sid限制条件，实现多租户SAAS
//
//        $childrenList = $this->getChildrenList();
//        $result = [];
//        foreach ($query->column($this->db) as $roleName) {
//            $this->getChildrenRecursive($roleName, $childrenList, $result);
//        }
//
//        if (empty($result)) {
//            return [];
//        }
//
//        $query = (new Query())->from($this->itemTable)->where([
//            'type' => Item::TYPE_PERMISSION,
//            'name' => array_keys($result),
//        ]);
//        $permissions = [];
//        foreach ($query->all($this->db) as $row) {
//            $permissions[$row['name']] = $this->populateItem($row);
//        }
//
//        return $permissions;
//    }
}