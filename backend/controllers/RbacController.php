<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\RbacRoleMeta;
use common\services\{
    AuditService,
    RbacCatalog,
    RbacService
};
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

final class RbacController extends AdminController
{
    protected string $permission = 'manageRbac';

    public function actionIndex(): string
    {
        $rows = RbacRoleMeta::find()
            ->orderBy([
                'level' => SORT_DESC,
                'label' => SORT_ASC,
            ])
            ->all();

        $auth = Yii::$app->authManager;
        $permissions = [];
        $assignments = [];

        foreach($rows as $row)
        {
            $permissions[$row->role_name] =
                array_keys(
                    $auth->getPermissionsByRole(
                        $row->role_name
                    )
                );

            $assignments[$row->role_name] =
                (int)Yii::$app->db
                    ->createCommand(
                        'SELECT COUNT(*) FROM {{%auth_assignment}} '
                        . 'WHERE item_name=:role',
                        [':role' => $row->role_name]
                    )
                    ->queryScalar();
        }

        return $this->render('index', [
            'rows' => $rows,
            'permissions' => $permissions,
            'assignments' => $assignments,
        ]);
    }

    public function actionEdit(
        ?string $name = null
    )
    {
        $service = new RbacService();
        $auth = Yii::$app->authManager;
        $actorId = (int)Yii::$app->user->id;

        $role = null;
        $meta = null;
        $isNew = $name === null;

        if(!$isNew)
        {
            $role = $auth->getRole((string)$name);
            $meta = RbacRoleMeta::findOne([
                'role_name' => $name,
            ]);

            if(!$role || !$meta)
            {
                throw new NotFoundHttpException();
            }

            $service->assertCanEditRole(
                $actorId,
                $name
            );
        }

        $selectedPermissions = $role
            ? array_keys(
                $auth->getPermissionsByRole(
                    $role->name
                )
            )
            : [];

        if(Yii::$app->request->isPost)
        {
            $label = trim(
                (string)Yii::$app->request->post(
                    'label'
                )
            );

            $level = (int)Yii::$app->request->post(
                'level'
            );

            $permissions = (array)Yii::$app->request->post(
                'permissions',
                []
            );

            $permissions = array_values(
                array_intersect(
                    RbacCatalog::names(),
                    array_map(
                        'strval',
                        $permissions
                    )
                )
            );

            if($label === '')
            {
                Yii::$app->session->setFlash(
                    'error',
                    'Укажите название роли.'
                );

                return $this->refresh();
            }

            $maxLevel = $service
                ->maxCustomRoleLevel($actorId);

            if(
                $level < 1
                || $level > $maxLevel
            )
            {
                Yii::$app->session->setFlash(
                    'error',
                    'Уровень роли должен быть от 1 до '
                    . $maxLevel
                    . '.'
                );

                return $this->refresh();
            }

            if($isNew)
            {
                $code = trim(
                    (string)Yii::$app->request->post(
                        'code'
                    )
                );

                if(
                    !preg_match(
                        '/^[a-z][a-z0-9_]{2,63}$/',
                        $code
                    )
                )
                {
                    throw new BadRequestHttpException(
                        'Некорректный код роли.'
                    );
                }

                if(
                    in_array(
                        $code,
                        [
                            RbacService::ROLE_USER,
                            RbacService::ROLE_ADMIN,
                        ],
                        true
                    )
                    || $auth->getRole($code)
                    || $auth->getPermission($code)
                )
                {
                    throw new BadRequestHttpException(
                        'Такой код уже используется.'
                    );
                }

                $role = $auth->createRole($code);
                $role->description = $label;

                if(!$auth->add($role))
                {
                    throw new \RuntimeException(
                        'Не удалось создать роль.'
                    );
                }

                $meta = new RbacRoleMeta([
                    'role_name' => $code,
                    'is_system' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            else
            {
                $role->description = $label;
                $auth->update($role->name, $role);
            }

            $meta->label = $label;
            $meta->level = $level;
            $meta->updated_at = date('Y-m-d H:i:s');

            if(!$meta->save())
            {
                throw new \RuntimeException(
                    'Не удалось сохранить метаданные роли.'
                );
            }

            $service->syncRolePermissions(
                $role,
                $permissions
            );

            AuditService::log(
                $isNew
                    ? 'admin.rbac.role.create'
                    : 'admin.rbac.role.update',
                'rbac_role',
                $role->name,
                [
                    'label' => $label,
                    'level' => $level,
                    'permissions' => $permissions,
                ]
            );

            Yii::$app->session->setFlash(
                'success',
                'Роль сохранена.'
            );

            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'role' => $role,
            'meta' => $meta,
            'isNew' => $isNew,
            'permissionGroups' => RbacCatalog::groups(),
            'selectedPermissions' => $selectedPermissions,
            'maxLevel' => $service
                ->maxCustomRoleLevel($actorId),
        ]);
    }

    public function actionDelete(
        string $name
    )
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $service = new RbacService();
        $service->assertCanEditRole(
            (int)Yii::$app->user->id,
            $name
        );

        $meta = RbacRoleMeta::findOne([
            'role_name' => $name,
        ]);

        if(!$meta)
        {
            throw new NotFoundHttpException();
        }

        if((int)$meta->is_system === 1)
        {
            throw new BadRequestHttpException(
                'Системную роль удалить нельзя.'
            );
        }

        $assigned = (int)Yii::$app->db
            ->createCommand(
                'SELECT COUNT(*) FROM {{%auth_assignment}} '
                . 'WHERE item_name=:role',
                [':role' => $name]
            )
            ->queryScalar();

        if($assigned > 0)
        {
            throw new BadRequestHttpException(
                'Сначала переназначьте пользователей этой роли.'
            );
        }

        $role = Yii::$app->authManager
            ->getRole($name);

        if(!$role)
        {
            throw new NotFoundHttpException();
        }

        Yii::$app->authManager
            ->remove($role);

        AuditService::log(
            'admin.rbac.role.delete',
            'rbac_role',
            $name
        );

        Yii::$app->session->setFlash(
            'success',
            'Роль удалена.'
        );

        return $this->redirect(['index']);
    }
}
