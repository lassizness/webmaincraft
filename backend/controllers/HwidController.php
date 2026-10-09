<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\{Hwid, User};
use common\services\{AuditService, RbacService};
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

final class HwidController extends AdminController
{
    protected string $permission = 'manageHwid';

    public function actionIndex(): string
    {
        $query = Hwid::find()
            ->alias('h')
            ->joinWith('users u')
            ->distinct()
            ->orderBy(['h.id' => SORT_DESC]);

        $q = trim((string)Yii::$app->request->get('q'));
        $banned = trim((string)Yii::$app->request->get('banned'));

        if($q !== '')
        {
            $query->andWhere([
                'or',
                ['like', 'u.username', $q],
                ['like', 'h.hwDiskId', $q],
                ['like', 'h.baseboardSerialNumber', $q],
                ['like', 'h.graphicCard', $q],
            ]);
        }

        if(in_array($banned, ['0', '1'], true))
        {
            $query->andWhere(['h.banned' => (int)$banned]);
        }

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 50],
            ]),
            'q' => $q,
            'banned' => $banned,
        ]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->find($id),
        ]);
    }

    public function actionBan(int $id, int $value)
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $model = $this->find($id);

        $rbac = new RbacService();
        foreach($model->users as $linkedUser)
        {
            $rbac->assertCanManageUser(
                (int)Yii::$app->user->id,
                (int)$linkedUser->id
            );
        }

        $model->banned = $value === 1 ? 1 : 0;
        $model->save(false, ['banned']);

        if($model->banned)
        {
            User::updateAll(
                ['accessToken' => null, 'serverID' => null],
                ['hwidId' => $model->id]
            );
        }

        AuditService::log(
            $model->banned ? 'admin.hwid.ban' : 'admin.hwid.unban',
            'hwid',
            (string)$id
        );

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionUnbind(int $userId)
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $user = User::findOne($userId);

        if(!$user)
        {
            throw new NotFoundHttpException();
        }

        (new RbacService())->assertCanManageUser(
            (int)Yii::$app->user->id,
            (int)$user->id
        );

        $oldHwidId = $user->hwidId;
        $user->hwidId = null;
        $user->accessToken = null;
        $user->serverID = null;
        $user->save(false, ['hwidId', 'accessToken', 'serverID']);

        AuditService::log(
            'admin.hwid.unbind',
            'user',
            (string)$userId,
            ['hwidId' => $oldHwidId]
        );

        Yii::$app->session->setFlash('success', 'HWID отвязан, launcher-токены пользователя сброшены.');

        return $oldHwidId
            ? $this->redirect(['view', 'id' => $oldHwidId])
            : $this->redirect(['index']);
    }

    private function find(int $id): Hwid
    {
        $model = Hwid::find()
            ->with(['users', 'logs'])
            ->where(['id' => $id])
            ->one();

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        return $model;
    }
}
