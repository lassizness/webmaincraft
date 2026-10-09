<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\ServerModerationBridge;
use common\services\{
    ModerationBridgeApiService,
    ServerModerationBridgeService
};
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\UnauthorizedHttpException;
use yii\web\Response;

final class InternalGameController extends Controller
{
    public $enableCsrfValidation = false;

    private ?ServerModerationBridge $bridge = null;

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'moderation-snapshot' => ['GET'],
                    'moderation-changes' => ['GET'],
                    'moderation-player' => ['GET'],
                    'bridge-heartbeat' => ['POST'],
                ],
            ],
        ];
    }

    public function beforeAction(
        $action
    ): bool
    {
        Yii::$app->response->format =
            Response::FORMAT_JSON;

        if(!parent::beforeAction($action))
        {
            return false;
        }

        $headers =
            Yii::$app->request->headers;

        try
        {
            $this->bridge =
                (new ServerModerationBridgeService())
                    ->authenticate(
                        (string)$headers->get(
                            'X-HiveTech-Server',
                            ''
                        ),
                        (string)$headers->get(
                            'Authorization',
                            ''
                        ),
                        Yii::$app->request->userIP
                    );
        }
        catch(UnauthorizedHttpException)
        {
            Yii::$app->response->statusCode = 401;
            Yii::$app->response->data = [
                'api_version' =>
                    ModerationBridgeApiService::API_VERSION,
                'error' => 'unauthorized',
            ];

            return false;
        }

        return true;
    }

    public function actionModerationSnapshot(): array
    {
        $result =
            (new ModerationBridgeApiService())
                ->snapshot(
                    (int)$this->bridge->server_id
                );

        return $this->envelope($result);
    }

    public function actionModerationChanges(): array
    {
        $after = filter_var(
            Yii::$app->request->get(
                'after',
                0
            ),
            FILTER_VALIDATE_INT
        );

        $limit = filter_var(
            Yii::$app->request->get(
                'limit',
                200
            ),
            FILTER_VALIDATE_INT
        );

        if(
            $after === false
            || $after < 0
            || $limit === false
            || $limit < 1
        )
        {
            return $this->badRequest(
                'invalid_revision_or_limit'
            );
        }

        $result =
            (new ModerationBridgeApiService())
                ->changes(
                    (int)$this->bridge->server_id,
                    (int)$after,
                    (int)$limit
                );

        return $this->envelope($result);
    }

    public function actionModerationPlayer(
        string $uuid
    ): array
    {
        if(
            !preg_match(
                '/^[0-9a-fA-F]{8}-'
                . '[0-9a-fA-F]{4}-'
                . '[0-9a-fA-F]{4}-'
                . '[0-9a-fA-F]{4}-'
                . '[0-9a-fA-F]{12}$/',
                $uuid
            )
        )
        {
            return $this->badRequest(
                'invalid_uuid'
            );
        }

        $result =
            (new ModerationBridgeApiService())
                ->player(
                    (int)$this->bridge->server_id,
                    strtolower($uuid)
                );

        return $this->envelope($result);
    }

    public function actionBridgeHeartbeat(): array
    {
        $payload = json_decode(
            Yii::$app->request->rawBody,
            true
        );

        if(!is_array($payload))
        {
            return $this->badRequest(
                'invalid_json'
            );
        }

        (new ServerModerationBridgeService())
            ->heartbeat(
                $this->bridge,
                $payload,
                Yii::$app->request->userIP
            );

        return $this->envelope([
            'accepted' => true,
            'current_revision' =>
                (new ModerationBridgeApiService())
                    ->currentRevision(),
        ]);
    }

    private function badRequest(
        string $error
    ): array
    {
        Yii::$app->response->statusCode = 400;

        return [
            'api_version' =>
                ModerationBridgeApiService::API_VERSION,
            'error' => $error,
        ];
    }

    private function envelope(
        array $data
    ): array
    {
        return [
            'api_version' =>
                ModerationBridgeApiService::API_VERSION,
            'server' => [
                'id' =>
                    (int)$this->bridge->server_id,
                'code' =>
                    (string)$this->bridge
                        ->server
                        ->code,
            ],
            'generated_at' => date(DATE_ATOM),
        ] + $data;
    }
}
