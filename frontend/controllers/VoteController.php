<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\{User, Vote, VoteService};
use common\services\voting\VotingService;
use frontend\components\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class VoteController extends Controller
{
    public function beforeAction($action): bool
    {
        if ($action->id === 'callback') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'callback', 'go'], 'roles' => ['?','@']],
                ],
            ],
        ];
    }

    public function actionIndex(?string $username = null): string
    {
        $services = VoteService::find()
            ->where(['enabled' => 1])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $player = null;
        $usernameValue = trim((string)$username);
        $usernameError = null;

        if (!Yii::$app->user->isGuest) {
            $player = User::findOne((int)Yii::$app->user->id);
            $usernameValue = $player !== null ? (string)$player->username : '';
        } elseif ($usernameValue !== '') {
            if (preg_match('/^[A-Za-z0-9_]{3,16}$/', $usernameValue) !== 1) {
                $usernameError = Yii::t('ui', 'Введите корректный Minecraft-ник.');
            } else {
                $player = User::findActiveByUsername($usernameValue);
                if ($player === null) {
                    $usernameError = Yii::t('ui', 'Игрок с таким ником не найден.');
                } else {
                    $usernameValue = (string)$player->username;
                }
            }
        }

        $statuses = [];
        $now = time();

        if ($player !== null) {
            foreach ($services as $service) {
                $lastVote = Vote::find()
                    ->where([
                        'service_id' => (int)$service->id,
                        'user_id' => (int)$player->id,
                    ])
                    ->andWhere(['not', ['rewarded_at' => null]])
                    ->orderBy(['rewarded_at' => SORT_DESC, 'id' => SORT_DESC])
                    ->one();

                $nextRewardAt = null;
                if ($lastVote !== null && $lastVote->rewarded_at !== null) {
                    $lastRewardAt = strtotime((string)$lastVote->rewarded_at);
                    if ($lastRewardAt !== false) {
                        $nextRewardAt = $lastRewardAt + max(1, (int)$service->reward_cooldown_seconds);
                    }
                }

                $statuses[(int)$service->id] = [
                    'available' => $nextRewardAt === null || $nextRewardAt <= $now,
                    'nextRewardAt' => $nextRewardAt,
                ];
            }
        }

        return $this->render('index', [
            'services' => $services,
            'player' => $player,
            'usernameValue' => $usernameValue,
            'usernameError' => $usernameError,
            'statuses' => $statuses,
        ]);
    }

    public function actionGo(string $service, string $username): Response
    {
        if (preg_match('/^[A-Za-z0-9_]{3,16}$/', $username) !== 1) {
            throw new BadRequestHttpException('Invalid Minecraft username.');
        }

        $model = $this->findService($service);

        try {
            $url = (new VotingService())->voteUrl($model, $username);
        } catch (\DomainException) {
            throw new NotFoundHttpException('Minecraft player not found.');
        }

        return $this->redirect($url);
    }

    public function actionCallback(string $service): Response
    {
        $model = $this->findService($service);

        try {
            $rewarded = (new VotingService())->acceptCallback($model);
        } catch (HttpException $e) {
            Yii::warning([
                'message' => $e->getMessage(),
                'service' => $service,
                'method' => Yii::$app->request->method,
                'queryKeys' => array_keys(Yii::$app->request->get()),
                'bodyKeys' => array_keys(Yii::$app->request->post()),
            ], 'voting');
            return $this->plainResponse($e->statusCode, $e->getMessage());
        } catch (\DomainException $e) {
            Yii::warning([
                'message' => $e->getMessage(),
                'service' => $service,
                'method' => Yii::$app->request->method,
                'queryKeys' => array_keys(Yii::$app->request->get()),
                'bodyKeys' => array_keys(Yii::$app->request->post()),
            ], 'voting');
            return $this->plainResponse(404, 'Vote player not found.');
        } catch (\Throwable $e) {
            Yii::error([
                'message' => $e->getMessage(),
                'service' => $service,
                'exception' => get_class($e),
            ], 'voting');
            return $this->plainResponse(500, 'Vote callback failed.');
        }

        if (!$rewarded) {
            Yii::info(['message' => 'Vote callback already rewarded', 'service' => $service], 'voting');
        }

        // Webhook providers only need a stable 200 acknowledgement. Reward idempotency
        // remains internal, so retries and TopCraft test requests still receive OK.
        return $this->plainResponse(200, 'OK');
    }

    private function plainResponse(int $statusCode, string $body): Response
    {
        $response = Yii::$app->response;
        $response->statusCode = $statusCode;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
        $response->data = $body;
        return $response;
    }

    private function findService(string $code): VoteService
    {
        $model = VoteService::findOne(['code' => $code, 'enabled' => 1]);
        if ($model === null) {
            throw new NotFoundHttpException('Voting service not found.');
        }
        return $model;
    }
}
