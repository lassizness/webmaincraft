<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\{
    Server,
    ServerModerationBridge
};
use common\services\{
    AuditService,
    ServerModerationBridgeService
};
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

final class ServerController extends AdminController
{
    protected string $permission = 'manageServers';

    public function actionIndex(): string
    {
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => Server::find()
                    ->with('moderationBridge')
                    ->orderBy(['sort' => SORT_ASC]),
            ]),
        ]);
    }

    public function actionEdit(
        ?int $id = null
    )
    {
        $model = $id
            ? Server::findOne($id)
            : new Server([
                'port' => 25565,
                'enabled' => 1,
                'sort' => 100,
                'rcon_env_key' => 'MAIN',
                'moderation_ban_command' =>
                    'ban {username} {reason}',
                'moderation_unban_command' =>
                    'pardon {username}',
            ]);

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        if(
            $model->load(
                Yii::$app->request->post()
            )
            && $model->save()
        )
        {
            return $this->redirect(['index']);
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    public function actionBridge(
        int $id
    ): string
    {
        $server = $this->findServer($id);

        $bridge = ServerModerationBridge::findOne([
            'server_id' => $server->id,
        ]);

        return $this->render('bridge', [
            'server' => $server,
            'bridge' => $bridge,
            'plainToken' =>
                Yii::$app->session->getFlash(
                    'moderationBridgeToken'
                ),
            'apiBaseUrl' =>
                rtrim(
                    (string)Yii::$app->params['appUrl'],
                    '/'
                )
                . '/internal/game',
        ]);
    }

    public function actionRotateBridgeToken(
        int $id
    )
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $server = $this->findServer($id);
        $service =
            new ServerModerationBridgeService();

        $existing = ServerModerationBridge::findOne([
            'server_id' => $server->id,
        ]);

        $token = $service->rotateToken(
            $server
        );

        $bridge = ServerModerationBridge::findOne([
            'server_id' => $server->id,
        ]);

        AuditService::log(
            $existing
                ? 'admin.server.bridge.rotate_token'
                : 'admin.server.bridge.create_token',
            'server',
            (string)$server->id,
            [
                'serverCode' => $server->code,
                'tokenHint' => $bridge?->token_hint,
            ]
        );

        Yii::$app->session->setFlash(
            'moderationBridgeToken',
            $token
        );

        Yii::$app->session->setFlash(
            'success',
            $existing
                ? 'Токен Moderation Bridge заменён. Старый токен больше не работает.'
                : 'Токен Moderation Bridge создан.'
        );

        return $this->redirect([
            'bridge',
            'id' => $server->id,
        ]);
    }

    public function actionToggleBridge(
        int $id
    )
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $server = $this->findServer($id);

        $enabled = filter_var(
            Yii::$app->request->post(
                'enabled'
            ),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        if($enabled === null)
        {
            throw new BadRequestHttpException();
        }

        $bridge =
            (new ServerModerationBridgeService())
                ->setEnabled(
                    $server,
                    $enabled
                );

        AuditService::log(
            'admin.server.bridge.toggle',
            'server',
            (string)$server->id,
            [
                'serverCode' => $server->code,
                'enabled' => (bool)$bridge->enabled,
            ]
        );

        Yii::$app->session->setFlash(
            'success',
            $bridge->enabled
                ? 'Moderation Bridge включён.'
                : 'Moderation Bridge отключён.'
        );

        return $this->redirect([
            'bridge',
            'id' => $server->id,
        ]);
    }

    private function findServer(
        int $id
    ): Server
    {
        $server = Server::findOne($id);

        if(!$server)
        {
            throw new NotFoundHttpException();
        }

        return $server;
    }
}
