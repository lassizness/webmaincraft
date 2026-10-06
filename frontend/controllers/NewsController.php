<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\News;
use common\services\ContentTranslationService;
use frontend\components\Controller;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

final class NewsController extends Controller
{
    public function actionIndex(): string
    {
        $query = News::find()
            ->where(['published' => 1])
            ->andWhere([
                'or',
                ['published_at' => null],
                ['<=', 'published_at', date('Y-m-d H:i:s')],
            ])
            ->orderBy([
                'published_at' => SORT_DESC,
                'id' => SORT_DESC,
            ]);

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 12,
                ],
            ]),
        ]);
    }

    public function actionView(string $slug): string
    {
        $model = News::find()
            ->where([
                'slug' => $slug,
                'published' => 1,
            ])
            ->andWhere([
                'or',
                ['published_at' => null],
                ['<=', 'published_at', date('Y-m-d H:i:s')],
            ])
            ->one();

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        ContentTranslationService::applyNews($model);

        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
