<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\{
    DeliveryQueue,
    ModerationAction,
    Server,
    User,
    UserPermission
};
use common\services\{
    AuditService,
    MinecraftOperatorService,
    RbacService,
    SessionTracker
};
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

final class UserController extends AdminController
{
    protected string $permission = 'viewUsers';

    public function actionIndex(): string
    {
        $query = User::find()
            ->orderBy(['id' => SORT_DESC]);

        $search = trim(
            (string)Yii::$app->request->get('q')
        );

        $status = trim(
            (string)Yii::$app->request->get('status')
        );

        $verified = trim(
            (string)Yii::$app->request->get('verified')
        );

        if($search !== '')
        {
            $query->andWhere([
                'or',
                ['like', 'username', $search],
                ['like', 'email', $search],
                ['uuid' => $search],
            ]);
        }

        if(in_array(
            $status,
            [
                User::STATUS_ACTIVE,
                User::STATUS_PENDING,
                User::STATUS_BLOCKED,
            ],
            true
        ))
        {
            $query->andWhere([
                'status' => $status,
            ]);
        }

        if(in_array(
            $verified,
            ['0', '1'],
            true
        ))
        {
            $query->andWhere([
                'email_verified' =>
                    (int)$verified,
            ]);
        }

        return $this->render('index', [
            'dataProvider' =>
                new ActiveDataProvider([
                    'query' => $query,
                    'pagination' => [
                        'pageSize' => 30,
                    ],
                ]),
            'q' => $search,
            'status' => $status,
            'verified' => $verified,
        ]);
    }

    public function actionView(
        int $id
    ): string
    {
        $model = $this->find($id);
        $rbac = new RbacService();

        $actorId = (int)Yii::$app->user->id;
        $canManageTarget =
            $actorId !== $id
            && $rbac->userLevel($id)
                < $rbac->userLevel($actorId);

        $canViewModeration =
            Yii::$app->user->can('viewModeration');

        $canManageMinecraftOperators =
            Yii::$app->user->can(
                'manageMinecraftOperators'
            );

        return $this->render('view', [
            'model' => $model,
            'currentRole' =>
                $rbac->primaryRoleName($id),
            'canManageTarget' =>
                $canManageTarget,
            'assignableRoles' =>
                Yii::$app->user->can('manageUserRoles')
                    ? $rbac->assignableRoles(
                        (int)Yii::$app->user->id
                    )
                    : [],
            'launcherPermissions' =>
                UserPermission::find()
                    ->where([
                        'uuid' => $model->uuid,
                    ])
                    ->orderBy('name')
                    ->all(),
            'moderationActions' =>
                $canViewModeration
                    ? ModerationAction::find()
                        ->with([
                            'server',
                            'creator',
                        ])
                        ->where([
                            'user_id' => $model->id,
                        ])
                        ->orderBy([
                            'id' => SORT_DESC,
                        ])
                        ->limit(10)
                        ->all()
                    : [],
            'activeModerationCount' =>
                $canViewModeration
                    ? ModerationAction::find()
                        ->where([
                            'user_id' => $model->id,
                            'status' =>
                                ModerationAction::STATUS_ACTIVE,
                        ])
                        ->count()
                    : 0,
            'minecraftServers' =>
                $canManageMinecraftOperators
                    ? Server::find()
                        ->where([
                            'enabled' => 1,
                        ])
                        ->orderBy([
                            'sort' => SORT_ASC,
                            'id' => SORT_ASC,
                        ])
                        ->all()
                    : [],
            'operatorDeliveries' =>
                $canManageMinecraftOperators
                    ? DeliveryQueue::find()
                        ->with('server')
                        ->where([
                            'user_id' => $model->id,
                            'delivery_type' =>
                                'rcon_command',
                            'payload' =>
                                MinecraftOperatorService::payloads(),
                        ])
                        ->orderBy([
                            'id' => SORT_DESC,
                        ])
                        ->limit(10)
                        ->all()
                    : [],
        ]);
    }

    public function actionMinecraftOperator(
        int $id
    )
    {
        $this->requirePermission(
            'manageMinecraftOperators'
        );

        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $user = $this->find($id);

        $serverId = (int)Yii::$app->request->post(
            'server_id'
        );

        $operation = trim(
            (string)Yii::$app->request->post(
                'operation'
            )
        );

        $server = Server::findOne([
            'id' => $serverId,
            'enabled' => 1,
        ]);

        if(!$server)
        {
            throw new \yii\web\BadRequestHttpException(
                'Сервер не найден или отключён.'
            );
        }

        if(!in_array(
            $operation,
            [
                MinecraftOperatorService::OPERATION_GRANT,
                MinecraftOperatorService::OPERATION_REVOKE,
            ],
            true
        ))
        {
            throw new \yii\web\BadRequestHttpException(
                'Неизвестная операция.'
            );
        }

        $queue =
            (new MinecraftOperatorService())
                ->enqueue(
                    $user,
                    $server,
                    $operation
                );

        AuditService::log(
            $operation
                === MinecraftOperatorService::OPERATION_GRANT
                    ? 'admin.minecraft.op.grant'
                    : 'admin.minecraft.op.revoke',
            'user',
            (string)$user->id,
            [
                'serverId' => (int)$server->id,
                'serverCode' => (string)$server->code,
                'queueId' => (int)$queue->id,
            ]
        );

        Yii::$app->session->setFlash(
            'success',
            $operation
                === MinecraftOperatorService::OPERATION_GRANT
                    ? 'Выдача OP поставлена в очередь.'
                    : 'Снятие OP поставлено в очередь.'
        );

        return $this->redirect([
            'view',
            'id' => $user->id,
        ]);
    }

    public function actionStatus(
        int $id,
        string $status
    )
    {
        $this->requirePermission(
            'manageUserStatus'
        );

        $actorId =
            (int)Yii::$app->user->id;

        (new RbacService())
            ->assertCanManageUser(
                $actorId,
                $id
            );

        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $model = $this->find($id);

        if(!in_array(
            $status,
            [
                User::STATUS_ACTIVE,
                User::STATUS_PENDING,
                User::STATUS_BLOCKED,
            ],
            true
        ))
        {
            throw new \yii\web\BadRequestHttpException();
        }

        $model->status = $status;

        if(
            $status ===
                User::STATUS_BLOCKED
        )
        {
            $model->accessToken = null;
            $model->serverID = null;

            (new SessionTracker())
                ->revokeAll($id);
        }

        $model->save(false, [
            'status',
            'accessToken',
            'serverID',
        ]);

        AuditService::log(
            'admin.user.status',
            'user',
            (string)$id,
            [
                'status' => $status,
            ]
        );

        return $this->redirect([
            'view',
            'id' => $id,
        ]);
    }

    public function actionAddLauncherPermission(
        int $id
    )
    {
        $this->requirePermission(
            'manageLauncherPermissions'
        );

        (new RbacService())
            ->assertCanManageUser(
                (int)Yii::$app->user->id,
                $id
            );

        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $user = $this->find($id);

        $name = trim(
            (string)Yii::$app->request->post(
                'permission'
            )
        );

        if(
            $name !== ''
            && strlen($name) <= 100
        )
        {
            $permission =
                new UserPermission([
                    'uuid' => $user->uuid,
                    'name' => $name,
                ]);

            $permission->save();

            AuditService::log(
                'admin.launcher.permission.add',
                'user',
                (string)$id,
                [
                    'permission' => $name,
                ]
            );
        }

        return $this->redirect([
            'view',
            'id' => $id,
        ]);
    }

    public function actionDeleteLauncherPermission(
        int $id,
        string $name
    )
    {
        $this->requirePermission(
            'manageLauncherPermissions'
        );

        (new RbacService())
            ->assertCanManageUser(
                (int)Yii::$app->user->id,
                $id
            );

        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $user = $this->find($id);

        UserPermission::deleteAll([
            'uuid' => $user->uuid,
            'name' => $name,
        ]);

        AuditService::log(
            'admin.launcher.permission.delete',
            'user',
            (string)$id,
            [
                'permission' => $name,
            ]
        );

        return $this->redirect([
            'view',
            'id' => $id,
        ]);
    }

    public function actionRole(
        int $id,
        string $role
    )
    {
        $this->requirePermission(
            'manageUserRoles'
        );

        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $service =
            new RbacService();

        $oldRole =
            $service->primaryRoleName($id);

        $service->assignRole(
            (int)Yii::$app->user->id,
            $id,
            $role
        );

        AuditService::log(
            'admin.user.role',
            'user',
            (string)$id,
            [
                'from' => $oldRole,
                'to' => $role,
            ]
        );

        return $this->redirect([
            'view',
            'id' => $id,
        ]);
    }

    private function requirePermission(
        string $permission
    ): void
    {
        if(
            !Yii::$app->user->can(
                $permission
            )
        )
        {
            throw new ForbiddenHttpException();
        }
    }

    private function find(
        int $id
    ): User
    {
        $model = User::findOne($id);

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        return $model;
    }
}
