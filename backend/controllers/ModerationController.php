<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\{
    ModerationAction,
    Server,
    User
};
use common\services\ModerationService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

final class ModerationController extends AdminController
{
    protected string $permission = 'viewModeration';

    public function actionIndex(): string
    {
        $query = ModerationAction::find()
            ->alias('m')
            ->with([
                'user',
                'server',
                'creator',
                'revoker',
            ])
            ->joinWith('user u')
            ->orderBy(['m.id' => SORT_DESC]);

        $q = trim((string)Yii::$app->request->get('q'));
        $type = trim((string)Yii::$app->request->get('type'));
        $status = trim((string)Yii::$app->request->get('status'));
        $scope = trim((string)Yii::$app->request->get('scope'));
        $serverId = (int)Yii::$app->request->get('server_id');

        if($q !== '')
        {
            $query->andWhere([
                'or',
                ['like', 'u.username', $q],
                ['like', 'm.reason', $q],
                ctype_digit($q)
                    ? ['m.id' => (int)$q]
                    : ['m.id' => -1],
            ]);
        }

        if(in_array($type, [
            ModerationAction::TYPE_WARN,
            ModerationAction::TYPE_MUTE,
            ModerationAction::TYPE_BAN,
        ], true))
        {
            $query->andWhere(['m.type' => $type]);
        }

        if(in_array($status, [
            ModerationAction::STATUS_ACTIVE,
            ModerationAction::STATUS_RECORDED,
            ModerationAction::STATUS_EXPIRED,
            ModerationAction::STATUS_REVOKED,
        ], true))
        {
            $query->andWhere(['m.status' => $status]);
        }

        if(in_array($scope, [
            ModerationAction::SCOPE_SERVER,
            ModerationAction::SCOPE_GLOBAL,
        ], true))
        {
            $query->andWhere(['m.scope' => $scope]);
        }

        if($serverId > 0)
        {
            $query->andWhere(['m.server_id' => $serverId]);
        }

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 50],
            ]),
            'servers' => Server::find()
                ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
                ->all(),
            'filters' => [
                'q' => $q,
                'type' => $type,
                'status' => $status,
                'scope' => $scope,
                'server_id' => $serverId,
            ],
        ]);
    }

    public function actionCreate(
        ?int $userId = null,
        ?string $type = null
    )
    {
        $user = $userId
            ? User::findOne($userId)
            : null;

        if(Yii::$app->request->isPost)
        {
            $targetUserId = (int)Yii::$app->request->post('user_id');

            if($targetUserId > 0)
            {
                $user = User::findOne($targetUserId);
            }
            else
            {
                $username = trim(
                    (string)Yii::$app->request->post('username')
                );

                $user = $username !== ''
                    ? User::findOne(['username' => $username])
                    : null;
            }

            if(!$user)
            {
                Yii::$app->session->setFlash(
                    'error',
                    'Пользователь не найден.'
                );

                return $this->refresh();
            }

            $type = trim(
                (string)Yii::$app->request->post('type')
            );

            $scope = trim(
                (string)Yii::$app->request->post(
                    'scope',
                    ModerationAction::SCOPE_SERVER
                )
            );

            $serverId = (int)Yii::$app->request->post('server_id');
            $reason = trim(
                (string)Yii::$app->request->post('reason')
            );
            $internalComment = trim(
                (string)Yii::$app->request->post('internal_comment')
            );

            $expiresAt = $this->resolveExpiry(
                $type,
                (string)Yii::$app->request->post(
                    'duration',
                    '1d'
                ),
                (string)Yii::$app->request->post(
                    'expires_at',
                    ''
                )
            );

            try
            {
                $action = (new ModerationService())->create(
                    (int)Yii::$app->user->id,
                    (int)$user->id,
                    $type,
                    $scope,
                    $serverId > 0 ? $serverId : null,
                    $reason,
                    $internalComment !== ''
                        ? $internalComment
                        : null,
                    $expiresAt
                );

                Yii::$app->session->setFlash(
                    'success',
                    'Наказание сохранено. RCON-синхронизация поставлена в очередь там, где команда настроена.'
                );

                return $this->redirect([
                    'view',
                    'id' => $action->id,
                ]);
            }
            catch(\Throwable $e)
            {
                Yii::$app->session->setFlash(
                    'error',
                    $e->getMessage()
                );
            }
        }

        $type = in_array($type, [
            ModerationAction::TYPE_WARN,
            ModerationAction::TYPE_MUTE,
            ModerationAction::TYPE_BAN,
        ], true)
            ? $type
            : ModerationAction::TYPE_WARN;

        return $this->render('create', [
            'user' => $user,
            'type' => $type,
            'servers' => Server::find()
                ->where(['enabled' => 1])
                ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
                ->all(),
        ]);
    }

    public function actionView(
        int $id
    ): string
    {
        $model = ModerationAction::find()
            ->with([
                'user',
                'server',
                'creator',
                'revoker',
                'history.actor',
            ])
            ->where(['id' => $id])
            ->one();

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionRevoke(
        int $id
    )
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        try
        {
            (new ModerationService())->revoke(
                (int)Yii::$app->user->id,
                $id,
                trim(
                    (string)Yii::$app->request->post(
                        'reason',
                        ''
                    )
                )
            );

            Yii::$app->session->setFlash(
                'success',
                'Наказание снято.'
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
            'view',
            'id' => $id,
        ]);
    }

    private function resolveExpiry(
        string $type,
        string $duration,
        string $custom
    ): ?string
    {
        if($type === ModerationAction::TYPE_WARN)
        {
            return null;
        }

        if($duration === 'permanent')
        {
            return null;
        }

        if($duration === 'custom')
        {
            $timestamp = strtotime($custom);

            if(!$timestamp)
            {
                throw new \DomainException(
                    'Укажите корректную дату окончания.'
                );
            }

            return date(
                'Y-m-d H:i:s',
                $timestamp
            );
        }

        $seconds = match($duration) {
            '1h' => 3600,
            '6h' => 6 * 3600,
            '1d' => 86400,
            '3d' => 3 * 86400,
            '7d' => 7 * 86400,
            '30d' => 30 * 86400,
            default => throw new \DomainException(
                'Некорректный срок наказания.'
            ),
        };

        return date(
            'Y-m-d H:i:s',
            time() + $seconds
        );
    }
}
