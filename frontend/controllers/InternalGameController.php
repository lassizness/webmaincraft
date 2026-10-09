<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\ServerModerationBridge;
use common\services\{
    ModerationBridgeApiService,
    ServerModerationBridgeService,
    TelemetryApiService
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
                    'telemetry-events' => ['POST'],
                    'telemetry-heartbeat' => ['POST'],
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

        Yii::$app->response->headers->set(
            'Cache-Control',
            'no-store, private'
        );
        Yii::$app->response->headers->set(
            'X-Content-Type-Options',
            'nosniff'
        );

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

        foreach([
            'bridge_version',
            'minecraft_version',
            'revision',
            'cached_actions',
            'online_players',
        ] as $required)
        {
            if(!array_key_exists($required, $payload))
            {
                return $this->badRequest(
                    'missing_' . $required
                );
            }
        }

        if(
            trim((string)$payload['bridge_version']) === ''
            || trim((string)$payload['minecraft_version']) === ''
            || filter_var(
                $payload['revision'],
                FILTER_VALIDATE_INT
            ) === false
            || (int)$payload['revision'] < 0
            || filter_var(
                $payload['cached_actions'],
                FILTER_VALIDATE_INT
            ) === false
            || (int)$payload['cached_actions'] < 0
            || filter_var(
                $payload['online_players'],
                FILTER_VALIDATE_INT
            ) === false
            || (int)$payload['online_players'] < 0
        )
        {
            return $this->badRequest(
                'invalid_heartbeat'
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

    public function actionTelemetryEvents(): array
    {
        $rawBody = Yii::$app->request->rawBody;

        if(strlen($rawBody) > 2097152)
        {
            Yii::$app->response->statusCode = 413;

            return [
                'api_version' =>
                    TelemetryApiService::API_VERSION,
                'error' => 'payload_too_large',
            ];
        }

        $payload = json_decode(
            $rawBody,
            true
        );

        if(!is_array($payload))
        {
            return $this->badRequest(
                'invalid_json'
            );
        }

        try
        {
            $result =
                (new TelemetryApiService())
                    ->ingest(
                        (int)$this->bridge->server_id,
                        $payload,
                        Yii::$app->request->userIP
                    );
        }
        catch(\InvalidArgumentException $e)
        {
            return $this->badRequest(
                $e->getMessage()
            );
        }

        return $this->envelope($result);
    }

    public function actionTelemetryHeartbeat(): array
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

        try
        {
            $result =
                (new TelemetryApiService())
                    ->heartbeat(
                        (int)$this->bridge->server_id,
                        $payload,
                        Yii::$app->request->userIP
                    );
        }
        catch(\InvalidArgumentException $e)
        {
            return $this->badRequest(
                $e->getMessage()
            );
        }

        return $this->envelope($result);
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
