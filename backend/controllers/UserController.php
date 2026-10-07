<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\{User, UserPermission};
use common\services\{AuditService, SessionTracker};
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

final class UserController extends AdminController
{
    protected string $permission = 'manageUsers';

    public function actionIndex(): string
    {
        $query = User::find()->orderBy(['id' => SORT_DESC]);

        $search = trim((string)Yii::$app->request->get('q'));
        $status = trim((string)Yii::$app->request->get('status'));
        $verified = trim((string)Yii::$app->request->get('verified'));

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
            $query->andWhere(['status' => $status]);
        }

        if(in_array($verified, ['0', '1'], true))
        {
            $query->andWhere([
                'email_verified' => (int)$verified,
            ]);
        }

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 30],
            ]),
            'q' => $search,
            'status' => $status,
            'verified' => $verified,
        ]);
    }

    public function actionView(int $id): string
    {
        $model = $this->find($id);

        return $this->render('view', [
            'model' => $model,
            'roles' => Yii::$app->authManager->getRolesByUser($id),
            'launcherPermissions' => UserPermission::find()
                ->where(['uuid' => $model->uuid])
                ->orderBy('name')
                ->all(),
        ]);
    }

    public function actionStatus(int $id, string $status)
    {
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

        if($status === User::STATUS_BLOCKED)
        {
            $model->accessToken = null;
            $model->serverID = null;
            (new SessionTracker())->revokeAll($id);
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
            ['status' => $status]
        );

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionAddLauncherPermission(int $id)
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $user = $this->find($id);
        $name = trim(
            (string)Yii::$app->request->post('permission')
        );

        if($name !== '' && strlen($name) <= 100)
        {
            $permission = new UserPermission([
                'uuid' => $user->uuid,
                'name' => $name,
            ]);

            $permission->save();

            AuditService::log(
                'admin.launcher.permission.add',
                'user',
                (string)$id,
                ['permission' => $name]
            );
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionDeleteLauncherPermission(
        int $id,
        string $name
    )
    {
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
            ['permission' => $name]
        );

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionRole(int $id, string $role)
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $authManager = Yii::$app->authManager;

        if(
            !in_array(
                $role,
                ['admin', 'moderator', 'user'],
                true
            )
            || !($newRole = $authManager->getRole($role))
        )
        {
            throw new \yii\web\BadRequestHttpException();
        }

        foreach($authManager->getRolesByUser($id) as $oldRole)
        {
            $authManager->revoke($oldRole, $id);
        }

        $authManager->assign($newRole, $id);

        AuditService::log(
            'admin.user.role',
            'user',
            (string)$id,
            ['role' => $role]
        );

        return $this->redirect(['view', 'id' => $id]);
    }

    private function find(int $id): User
    {
        $model = User::findOne($id);

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        return $model;
    }
}
