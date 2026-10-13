<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\{
    Setting,
    ShopPurchase,
    User,
    UserToken,
    VoteService,
    WebSession
};
use common\services\{
    AchievementProfileService,
    AuditService,
    SessionTracker,
    TokenService,
    WalletService
};
use frontend\components\Controller;
use frontend\models\{
    ChangePasswordForm,
    ProfileForm
};
use Yii;
use yii\filters\AccessControl;

final class AccountController extends Controller
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

    public function actionIndex(): string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $achievementsEnabled =
            (string)Setting::value(
                'achievements.enabled',
                '1'
            ) === '1';

        $achievementService =
            $achievementsEnabled
                ? new AchievementProfileService()
                : null;

        return $this->render('index', [
            'user' => $user,
            'wallet' => (new WalletService())->wallet((int)$user->id),
            'purchases' => ShopPurchase::find()
                ->with('product')
                ->where(['user_id' => $user->id])
                ->orderBy(['id' => SORT_DESC])
                ->limit(5)
                ->all(),
            'voteServices' => VoteService::find()
                ->where(['enabled' => 1])
                ->orderBy([
                    'sort' => SORT_ASC,
                    'id' => SORT_ASC,
                ])
                ->all(),
            'achievementsEnabled' =>
                $achievementsEnabled,
            'achievementSummary' =>
                $achievementService
                    ? $achievementService->summary(
                        (int)$user->id
                    )
                    : null,
            'recentAchievements' =>
                $achievementService
                    ? $achievementService
                        ->recentUnlocksForUser(
                            (int)$user->id,
                            6
                        )
                    : [],
            'achievementStats' =>
                $achievementService
                    ? $achievementService->statsForUser(
                        (int)$user->id
                    )
                    : [],
        ]);
    }

    public function actionAchievements()
    {
        if(
            (string)Setting::value(
                'achievements.enabled',
                '1'
            ) !== '1'
        )
        {
            throw new \yii\web\NotFoundHttpException();
        }

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $service = new AchievementProfileService();
        $profileSettings = $service->profileSettings((int)$user->id);

        if(Yii::$app->request->isPost)
        {
            $profileSettings->public_achievements =
                Yii::$app->request->post('public_achievements') === '1'
                    ? 1
                    : 0;
            $profileSettings->public_playtime =
                Yii::$app->request->post('public_playtime') === '1'
                    ? 1
                    : 0;
            $profileSettings->public_ranking =
                Yii::$app->request->post('public_ranking') === '1'
                    ? 1
                    : 0;
            $profileSettings->updated_at = date('Y-m-d H:i:s');
            $profileSettings->save(false);

            Yii::$app->session->setFlash(
                'success',
                Yii::t('ui', 'Настройки публичного профиля сохранены.')
            );

            return $this->redirect(['achievements']);
        }

        return $this->render('achievements', [
            'user' => $user,
            'catalog' => $service->catalog(),
            'unlocks' => $service->unlocksForUser((int)$user->id),
            'progress' => $service->progressForUser((int)$user->id),
            'summary' => $service->summary((int)$user->id),
            'rank' => $service->rankForUser((int)$user->id),
            'profileSettings' => $profileSettings,
            'publicRatingsEnabled' =>
                (string)Setting::value(
                    'ratings.public_enabled',
                    '0'
                ) === '1',
        ]);
    }

    public function actionSettings()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $model = new ProfileForm([
            'email' => $user->email,
        ]);

        if(
            $model->load(Yii::$app->request->post())
            && $model->validate()
            && mb_strtolower($model->email)
                !== mb_strtolower($user->email)
        )
        {
            $user->email = mb_strtolower($model->email);
            $user->email_verified = 0;
            $user->save(false, [
                'email',
                'email_verified',
            ]);

            $raw = (new TokenService())->issue(
                (int)$user->id,
                UserToken::TYPE_EMAIL_VERIFY,
                86400
            );

            $url = Yii::$app->params['appUrl']
                . '/verify-email?token='
                . rawurlencode($raw);

            try
            {
                Yii::$app->mailer
                    ->compose('verify-email-change', [
                        'user' => $user,
                        'url' => $url,
                    ])
                    ->setTo($user->email)
                    ->setSubject(
                        Yii::t(
                            'app',
                            'Подтвердите новый email HiveTech'
                        )
                    )
                    ->send();
            }
            catch(\Throwable $mailError)
            {
                Yii::error([
                    'message' => 'Failed to send email change verification',
                    'userId' => (int)$user->id,
                    'email' => $user->email,
                    'error' => $mailError->getMessage(),
                ], 'mail');
            }

            AuditService::log(
                'user.email_change',
                'user',
                (string)$user->id
            );

            Yii::$app->session->setFlash(
                'success',
                Yii::t(
                    'ui',
                    'Email изменён. Аккаунт остаётся активным; подтвердите новый адрес, когда получите письмо.'
                )
            );

            return $this->redirect(['settings']);
        }

        return $this->render('settings', [
            'model' => $model,
        ]);
    }

    public function actionSecurity()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $model = new ChangePasswordForm();

        if(
            $model->load(Yii::$app->request->post())
            && $model->validate()
        )
        {
            if(!$user->validatePassword($model->currentPassword))
            {
                $model->addError(
                    'currentPassword',
                    Yii::t('ui', 'Текущий пароль неверен.')
                );
            }
            else
            {
                $user->setPassword($model->password);
                $user->save(false, [
                    'password',
                    'accessToken',
                    'serverID',
                ]);

                (new SessionTracker())->revokeAll((int)$user->id);

                AuditService::log(
                    'user.password_change',
                    'user',
                    (string)$user->id
                );

                Yii::$app->user->logout();

                Yii::$app->session->setFlash(
                    'success',
                    Yii::t(
                        'ui',
                        'Пароль изменён. Войдите снова.'
                    )
                );

                return $this->redirect(['/auth/login']);
            }
        }

        return $this->render('security', [
            'model' => $model,
        ]);
    }

    public function actionSessions(): string
    {
        return $this->render('sessions', [
            'sessions' => WebSession::find()
                ->where([
                    'user_id' => Yii::$app->user->id,
                ])
                ->orderBy(['id' => SORT_DESC])
                ->limit(30)
                ->all(),
        ]);
    }

    public function actionRevokeSession(int $id)
    {
        if(Yii::$app->request->isPost)
        {
            (new SessionTracker())->revoke(
                $id,
                (int)Yii::$app->user->id
            );
        }

        return $this->redirect(['sessions']);
    }
}
