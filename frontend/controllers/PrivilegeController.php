<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\UserPrivilege;
use frontend\components\Controller;
use Yii;
use yii\filters\AccessControl;

final class PrivilegeController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' =>
                    AccessControl::class,
                'only' => [
                    'index',
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [
                            '@',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render(
            'index',
            [
                'privileges' =>
                    UserPrivilege::find()
                        ->with(
                            [
                                'group',
                                'server',
                            ]
                        )
                        ->where(
                            [
                                'user_id' =>
                                    Yii::$app->user->id,
                            ]
                        )
                        ->orderBy(
                            [
                                'id' =>
                                    SORT_DESC,
                            ]
                        )
                        ->all(),
            ]
        );
    }
}
