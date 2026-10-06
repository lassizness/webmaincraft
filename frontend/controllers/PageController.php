<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\Page;
use common\services\ContentTranslationService;
use frontend\components\Controller;
use yii\web\NotFoundHttpException;

final class PageController extends Controller
{
    public function actionView(string $slug): string
    {
        $model = Page::findOne([
            'slug' => $slug,
            'published' => 1,
        ]);

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        ContentTranslationService::applyPage($model);

        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
