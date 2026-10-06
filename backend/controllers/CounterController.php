<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\SiteCounter;
use common\services\SiteCounterService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

final class CounterController extends AdminController
{
    protected string $permission = 'manageSettings';

    public function actionIndex(): string
    {
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => SiteCounter::find()->orderBy([
                    'sort' => SORT_ASC,
                    'id' => SORT_ASC,
                ]),
            ]),
        ]);
    }

    public function actionCreate()
    {
        return $this->edit(
            new SiteCounter([
                'enabled' => 1,
                'sort' => 100,
            ])
        );
    }

    public function actionUpdate(int $id)
    {
        return $this->edit($this->find($id));
    }

    public function actionDelete(int $id)
    {
        if(Yii::$app->request->isPost)
        {
            $this->find($id)->delete();

            SiteCounterService::flushCache();

            Yii::$app->session->setFlash(
                'success',
                'Счётчик удалён.'
            );
        }

        return $this->redirect(['index']);
    }

    private function edit(SiteCounter $model)
    {
        if(
            $model->load(Yii::$app->request->post())
            && $model->save()
        )
        {
            SiteCounterService::flushCache();

            Yii::$app->session->setFlash(
                'success',
                'Счётчик сохранён.'
            );

            return $this->redirect(['index']);
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    private function find(int $id): SiteCounter
    {
        $model = SiteCounter::findOne($id);

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        return $model;
    }
}
