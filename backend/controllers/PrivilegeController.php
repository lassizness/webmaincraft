<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\{
    PrivilegeGroup,
    PrivilegeHistory,
    Server,
    User,
    UserPrivilege
};
use common\services\{
    AuditService,
    PrivilegeService
};
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

final class PrivilegeController extends AdminController
{
    protected string $permission =
        'managePrivileges';

    public function actionIndex(): string
    {
        return $this->render(
            'index',
            [
                'groups' =>
                    PrivilegeGroup::find()
                        ->orderBy(
                            [
                                'sort' =>
                                    SORT_ASC,
                                'level' =>
                                    SORT_ASC,
                            ]
                        )
                        ->all(),
                'servers' =>
                    Server::find()
                        ->where(
                            [
                                'enabled' => 1,
                            ]
                        )
                        ->orderBy(
                            [
                                'sort' =>
                                    SORT_ASC,
                            ]
                        )
                        ->all(),
                'dataProvider' =>
                    new ActiveDataProvider(
                        [
                            'query' =>
                                UserPrivilege::find()
                                    ->with(
                                        [
                                            'user',
                                            'server',
                                            'group',
                                        ]
                                    )
                                    ->orderBy(
                                        [
                                            'id' =>
                                                SORT_DESC,
                                        ]
                                    ),
                            'pagination' =>
                                [
                                    'pageSize' =>
                                        30,
                                ],
                        ]
                    ),
            ]
        );
    }

    public function actionGroup(
        ?int $id = null
    )
    {
        $model =
            $id
                ? PrivilegeGroup::findOne(
                    $id
                )
                : new PrivilegeGroup(
                    [
                        'enabled' => 1,
                        'level' => 10,
                        'sort' => 100,
                    ]
                );

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        if(
            $model->load(
                Yii::$app->request->post()
            )
            && $model->save()
        )
        {
            AuditService::log(
                'admin.privilege.group.save',
                'privilege_group',
                (string)$model->id
            );

            return $this->redirect(
                [
                    'index',
                ]
            );
        }

        return $this->render(
            'group-form',
            [
                'model' =>
                    $model,
            ]
        );
    }

    public function actionGrant()
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $username =
            trim(
                (string)Yii::$app->request->post(
                    'username'
                )
            );

        $groupId =
            (int)Yii::$app->request->post(
                'group_id'
            );

        $serverId =
            (int)Yii::$app->request->post(
                'server_id'
            );

        $days =
            (int)Yii::$app->request->post(
                'days',
                30
            );

        $user =
            User::findOne(
                [
                    'username' =>
                        $username,
                ]
            );

        $group =
            PrivilegeGroup::findOne(
                $groupId
            );

        $server =
            Server::findOne(
                $serverId
            );

        if(
            !$user
            || !$group
            || !$server
        )
        {
            Yii::$app->session
                ->setFlash(
                    'error',
                    'Пользователь, сервер или статус не найден.'
                );

            return $this->redirect(
                [
                    'index',
                ]
            );
        }

        try
        {
            $row =
                (
                    new PrivilegeService()
                )->grantByAdmin(
                    (int)$user->id,
                    (int)$server->id,
                    $group,
                    $days
                );

            AuditService::log(
                'admin.privilege.grant',
                'user_privilege',
                (string)$row->id,
                [
                    'userId' =>
                        (int)$user->id,
                    'groupId' =>
                        (int)$group->id,
                    'serverId' =>
                        (int)$server->id,
                    'days' =>
                        $days,
                ]
            );

            Yii::$app->session
                ->setFlash(
                    'success',
                    'Статус выдан. Синхронизация поставлена в очередь.'
                );
        }
        catch(\Throwable $e)
        {
            Yii::$app->session
                ->setFlash(
                    'error',
                    $e->getMessage()
                );
        }

        return $this->redirect(
            [
                'index',
            ]
        );
    }

    public function actionRevoke(
        int $id
    )
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        try
        {
            (
                new PrivilegeService()
            )->revoke(
                $id
            );

            AuditService::log(
                'admin.privilege.revoke',
                'user_privilege',
                (string)$id
            );

            Yii::$app->session
                ->setFlash(
                    'success',
                    'Статус отозван. Синхронизация поставлена в очередь.'
                );
        }
        catch(\Throwable $e)
        {
            Yii::$app->session
                ->setFlash(
                    'error',
                    $e->getMessage()
                );
        }

        return $this->redirect(
            [
                'index',
            ]
        );
    }

    public function actionHistory(): string
    {
        return $this->render(
            'history',
            [
                'dataProvider' =>
                    new ActiveDataProvider(
                        [
                            'query' =>
                                PrivilegeHistory::find()
                                    ->with(
                                        [
                                            'user',
                                            'server',
                                            'fromGroup',
                                            'toGroup',
                                        ]
                                    )
                                    ->orderBy(
                                        [
                                            'id' =>
                                                SORT_DESC,
                                        ]
                                    ),
                            'pagination' =>
                                [
                                    'pageSize' =>
                                        50,
                                ],
                        ]
                    ),
            ]
        );
    }
}
