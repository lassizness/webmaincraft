<?php

declare(strict_types=1);

namespace frontend\controllers;

use frontend\components\Controller;
use yii\filters\AccessControl;

final class CosmeticController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->redirect([
            '/skin/index',
        ]);
    }
}
