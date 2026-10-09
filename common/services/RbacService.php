<?php

declare(strict_types=1);

namespace common\services;

use common\models\RbacRoleMeta;
use Yii;
use yii\rbac\Role;
use yii\web\ForbiddenHttpException;

final class RbacService
{
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    public function primaryRoleName(int $userId): string
    {
        $roles = Yii::$app->authManager->getRolesByUser($userId);

        if(!$roles)
        {
            return self::ROLE_USER;
        }

        $best = self::ROLE_USER;
        $bestLevel = 0;

        foreach($roles as $role)
        {
            $level = $this->roleLevel($role->name);

            if($level >= $bestLevel)
            {
                $best = $role->name;
                $bestLevel = $level;
            }
        }

        return $best;
    }

    public function roleLevel(string $roleName): int
    {
        if($roleName === self::ROLE_USER)
        {
            return 0;
        }

        $meta = RbacRoleMeta::findOne(['role_name' => $roleName]);

        return $meta ? (int)$meta->level : 0;
    }

    public function userLevel(int $userId): int
    {
        return $this->roleLevel($this->primaryRoleName($userId));
    }

    public function assertCanManageUser(int $actorId, int $targetId): void
    {
        if($actorId === $targetId)
        {
            throw new ForbiddenHttpException(
                'Нельзя выполнять это действие над собственной учётной записью.'
            );
        }

        if($this->userLevel($targetId) >= $this->userLevel($actorId))
        {
            throw new ForbiddenHttpException(
                'Нельзя управлять сотрудником с равным или более высоким уровнем доступа.'
            );
        }
    }

    public function assertCanModerate(int $actorId, int $targetId): void
    {
        $this->assertCanManageUser($actorId, $targetId);
    }

    public function assertCanAssignRole(
        int $actorId,
        int $targetId,
        string $roleName
    ): void
    {
        $this->assertCanManageUser($actorId, $targetId);

        $role = Yii::$app->authManager->getRole($roleName);

        if(!$role)
        {
            throw new \InvalidArgumentException('Роль не найдена.');
        }

        if($this->roleLevel($roleName) > $this->userLevel($actorId))
        {
            throw new ForbiddenHttpException(
                'Нельзя назначить роль выше собственного уровня доступа.'
            );
        }
    }

    public function assignRole(
        int $actorId,
        int $targetId,
        string $roleName
    ): void
    {
        $this->assertCanAssignRole($actorId, $targetId, $roleName);

        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);

        if(!$role)
        {
            throw new \InvalidArgumentException('Роль не найдена.');
        }

        $auth->revokeAll($targetId);
        $auth->assign($role, $targetId);
    }

    public function assignableRoles(int $actorId): array
    {
        $actorLevel = $this->userLevel($actorId);
        $result = [
            self::ROLE_USER => [
                'name' => self::ROLE_USER,
                'label' => 'Игрок',
                'level' => 0,
            ],
        ];

        $rows = RbacRoleMeta::find()
            ->where(['<=', 'level', $actorLevel])
            ->orderBy(['level' => SORT_ASC, 'label' => SORT_ASC])
            ->all();

        foreach($rows as $row)
        {
            if(Yii::$app->authManager->getRole($row->role_name))
            {
                $result[$row->role_name] = [
                    'name' => $row->role_name,
                    'label' => $row->label,
                    'level' => (int)$row->level,
                ];
            }
        }

        return $result;
    }

    public function assertCanEditRole(int $actorId, string $roleName): void
    {
        if(!Yii::$app->authManager->checkAccess(
            $actorId,
            'manageRbac'
        ))
        {
            throw new ForbiddenHttpException();
        }

        if($roleName === self::ROLE_ADMIN)
        {
            throw new ForbiddenHttpException(
                'Системная роль администратора защищена от редактирования.'
            );
        }

        if($this->roleLevel($roleName) >= $this->userLevel($actorId))
        {
            throw new ForbiddenHttpException(
                'Нельзя редактировать роль равного или более высокого уровня.'
            );
        }
    }

    public function maxCustomRoleLevel(int $actorId): int
    {
        return max(1, $this->userLevel($actorId) - 1);
    }

    public function syncRolePermissions(
        Role $role,
        array $permissionNames
    ): void
    {
        $auth = Yii::$app->authManager;
        $auth->removeChildren($role);

        $userRole = $auth->getRole(self::ROLE_USER);
        if($userRole)
        {
            $auth->addChild($role, $userRole);
        }

        $adminPanel = $auth->getPermission('adminPanel');
        if($adminPanel)
        {
            $auth->addChild($role, $adminPanel);
        }

        $allowed = array_flip(RbacCatalog::names());

        foreach(array_unique($permissionNames) as $name)
        {
            if(!isset($allowed[$name]))
            {
                continue;
            }

            $permission = $auth->getPermission($name);

            if($permission)
            {
                $auth->addChild($role, $permission);
            }
        }
    }
}
