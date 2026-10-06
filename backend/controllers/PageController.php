<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\Page;
use common\services\ContentAutoTranslationService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

final class PageController extends AdminController
{
    protected string $permission = 'manageContent';

    public function actionIndex(): string
    {
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => Page::find()->orderBy([
                    'sort' => SORT_ASC,
                    'id' => SORT_DESC,
                ]),
            ]),
        ]);
    }

    public function actionCreate()
    {
        return $this->edit(
            new Page([
                'published' => 1,
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
        }

        return $this->redirect(['index']);
    }

    private function edit(Page $model)
    {
        if(
            $model->load(Yii::$app->request->post())
            && $model->save()
        )
        {
            $errors = (new ContentAutoTranslationService())
                ->translatePage($model);

            if($errors)
            {
                Yii::$app->session->setFlash(
                    'warning',
                    'Страница сохранена, но автоматический перевод выполнен не полностью: '
                    . implode(' | ', $errors)
                );
            }
            else
            {
                Yii::$app->session->setFlash(
                    'success',
                    'Страница сохранена. EN и DE переводы обновлены автоматически.'
                );
            }

            return $this->redirect(['index']);
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    private function find(int $id): Page
    {
        $model = Page::findOne($id);

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        return $model;
    }
}
