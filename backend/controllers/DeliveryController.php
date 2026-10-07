<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\DeliveryQueue;
use common\services\AuditService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

final class DeliveryController extends AdminController
{
    protected string $permission = 'manageDelivery';

    public function actionIndex(): string
    {
        $query = DeliveryQueue::find()
            ->with(['user', 'server', 'purchase'])
            ->orderBy(['id' => SORT_DESC]);

        $status = trim((string)Yii::$app->request->get('status'));
        $q = trim((string)Yii::$app->request->get('q'));

        if(in_array($status, ['pending', 'processing', 'done', 'failed'], true))
        {
            $query->andWhere(['status' => $status]);
        }

        if($q !== '')
        {
            if(ctype_digit($q))
            {
                $query->andWhere([
                    'or',
                    ['id' => (int)$q],
                    ['purchase_id' => (int)$q],
                    ['user_id' => (int)$q],
                ]);
            }
            else
            {
                $query->joinWith('user')
                    ->andWhere(['like', 'users.username', $q]);
            }
        }

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 50],
            ]),
            'status' => $status,
            'q' => $q,
        ]);
    }

    public function actionRetry(int $id)
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $row = $this->find($id);
        $row->status = 'pending';
        $row->available_at = null;
        $row->processed_at = null;
        $row->last_error = null;
        $row->save(false);

        AuditService::log(
            'admin.delivery.retry',
            'delivery_queue',
            (string)$id,
            ['attempts' => (int)$row->attempts]
        );

        Yii::$app->session->setFlash('success', 'Задача возвращена в очередь.');

        return $this->redirect(['index']);
    }

    public function actionForceRetry(int $id)
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $row = $this->find($id);
        $row->status = 'pending';
        $row->attempts = 0;
        $row->available_at = null;
        $row->processed_at = null;
        $row->last_error = null;
        $row->save(false);

        AuditService::log(
            'admin.delivery.force_retry',
            'delivery_queue',
            (string)$id
        );

        Yii::$app->session->setFlash('success', 'Счётчик попыток сброшен, задача возвращена в очередь.');

        return $this->redirect(['index']);
    }

    private function find(int $id): DeliveryQueue
    {
        $row = DeliveryQueue::findOne($id);

        if(!$row)
        {
            throw new NotFoundHttpException();
        }

        return $row;
    }
}
