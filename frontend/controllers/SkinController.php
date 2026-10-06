<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\{
    Server,
    UserCosmetic
};
use common\services\{
    CosmeticService,
    PrivilegeAccessService,
    SkinService
};
use frontend\components\Controller;
use Yii;
use yii\filters\{
    AccessControl,
    VerbFilter
};
use yii\web\{
    NotFoundHttpException,
    UploadedFile
};

final class SkinController extends Controller
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'nick-color' => ['post'],
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        if(!$user)
        {
            return $this->redirect([
                '/auth/login',
            ]);
        }

        /*
         * Загрузка скина / плаща.
         */
        if(Yii::$app->request->isPost)
        {
            $service = new SkinService();

            foreach(
                ['skin', 'cloak']
                as $type
            )
            {
                $file =
                    UploadedFile::getInstanceByName(
                        $type
                    );

                if(!$file)
                {
                    continue;
                }

                try
                {
                    $service->save(
                        $user,
                        $file,
                        $type
                    );

                    Yii::$app->session->setFlash(
                        'success',
                        (
                            $type === 'skin'
                                ? 'Скин'
                                : 'Плащ'
                        )
                        . ' сохранён.'
                    );
                }
                catch(\Throwable $e)
                {
                    Yii::$app->session->setFlash(
                        'error',
                        $e->getMessage()
                    );
                }
            }

            return $this->refresh();
        }


        $server =
            $this->server();

        $userId =
            (int)$user->id;

        $serverId =
            (int)$server->id;

        $access =
            new PrivilegeAccessService();

        $privilege =
            $access->activePrivilege(
                $userId,
                $serverId
            );

        $cosmetic =
            UserCosmetic::findOne([
                'user_id' => $userId,
                'server_id' => $serverId,
            ]);

        return $this->render(
            'index',
            [
                'user' => $user,
                'server' => $server,
                'privilege' => $privilege,
                'cosmetic' => $cosmetic,
                'colors' =>
                    CosmeticService::COLORS,
                'canCloak' =>
                    $access->canUseCloak(
                        $userId,
                        $serverId
                    ),
                'canNickColor' =>
                    $access->canUseNickColor(
                        $userId,
                        $serverId
                    ),
            ]
        );
    }


    public function actionNickColor()
    {
        $server =
            $this->server();

        $color =
            trim(
                (string)Yii::$app->request
                    ->post(
                        'color',
                        ''
                    )
            );

        if($color === 'reset')
        {
            $color = null;
        }

        try
        {
            (
                new CosmeticService()
            )->setNickColor(
                (int)Yii::$app->user->id,
                (int)$server->id,
                $color
            );

            Yii::$app->session->setFlash(
                'success',
                $color === null
                    ? 'Цвет ника сброшен.'
                    : 'Цвет ника сохранён. Изменение будет применено на игровом сервере.'
            );
        }
        catch(\Throwable $e)
        {
            Yii::$app->session->setFlash(
                'error',
                $e->getMessage()
            );
        }

        return $this->redirect([
            'index',
        ]);
    }


    public function actionDelete(
        string $type
    )
    {
        $user =
            Yii::$app->user->identity;

        if(!$user)
        {
            return $this->redirect([
                '/auth/login',
            ]);
        }

        $paths = [
            'skin' => Yii::getAlias(
                '@storage/textures/skins/'
                . $user->uuid
                . '.png'
            ),
            'cloak' => Yii::getAlias(
                '@storage/textures/cloaks/'
                . $user->uuid
                . '.png'
            ),
        ];

        if(!isset($paths[$type]))
        {
            throw new \yii\web\BadRequestHttpException(
                'Неизвестный тип текстуры.'
            );
        }

        $path =
            $paths[$type];

        if(
            is_file($path)
            && !unlink($path)
        )
        {
            Yii::$app->session->setFlash(
                'error',
                'Не удалось удалить файл.'
            );

            return $this->redirect([
                'index',
            ]);
        }

        Yii::$app->session->setFlash(
            'success',
            $type === 'skin'
                ? 'Скин удалён.'
                : 'Плащ удалён.'
        );

        return $this->redirect([
            'index',
        ]);
    }


    private function server(): Server
    {
        $server =
            Server::find()
                ->where([
                    'enabled' => 1,
                ])
                ->orderBy([
                    'sort' => SORT_ASC,
                    'id' => SORT_ASC,
                ])
                ->one();

        if(!$server)
        {
            throw new NotFoundHttpException(
                'Игровой сервер не найден.'
            );
        }

        return $server;
    }
}
