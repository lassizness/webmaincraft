<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\{Setting, User, UserToken};
use common\services\{AuditService, LoginAttemptService, RecaptchaService, RegistrationService, SessionTracker, TokenService};
use frontend\components\Controller;
use frontend\models\{LoginForm, PasswordResetRequestForm, ResetPasswordForm, SignupForm};
use Yii;
use yii\filters\VerbFilter;

final class AuthController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionLogin()
    {
        if(!Yii::$app->user->isGuest) return $this->goHome();

        $model = new LoginForm();
        $recaptcha = new RecaptchaService();

        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $captchaPassed = true;

            if($recaptcha->isEnabledFor(RecaptchaService::SCOPE_LOGIN))
            {
                if(!$recaptcha->isConfigured())
                {
                    Yii::error('reCAPTCHA is enabled for login but is not configured', 'recaptcha');
                    $model->addError('password', 'Защита от ботов временно недоступна.');
                    $captchaPassed = false;
                }
                elseif(!$recaptcha->verify(
                    Yii::$app->request->post('g-recaptcha-response'),
                    Yii::$app->request->userIP
                ))
                {
                    $model->addError('password', 'Подтвердите, что вы не робот.');
                    $captchaPassed = false;
                }
            }

            if($captchaPassed)
            {
                $ip = (string)Yii::$app->request->userIP;
                $service = new LoginAttemptService();

                if($service->isBlocked($model->login, $ip))
                {
                    $model->addError('password', 'Слишком много попыток. Повторите через 15 минут.');
                }
                else
                {
                    $user = $model->getUser();
                    $ok = $user
                        && $user->status === User::STATUS_ACTIVE
                        && $user->validatePassword($model->password);

                    $service->record($model->login, $ip, (bool)$ok);

                    if($ok && Yii::$app->user->login($user, $model->rememberMe ? 30 * 86400 : 0))
                    {
                        $user->last_login_at = date('Y-m-d H:i:s');
                        $user->save(false, ['last_login_at']);
                        (new SessionTracker())->register((int)$user->id);
                        AuditService::log('web.login', 'user', (string)$user->id);
                        return $this->redirect(['/account/index']);
                    }

                    $model->addError('password', 'Неверный логин/почта или пароль.');
                }
            }
        }

        return $this->render('login', [
            'model' => $model,
            'recaptcha' => $recaptcha,
        ]);
    }

    public function actionRegister()
    {
        if((string)Setting::value('registration.enabled', '1') !== '1')
        {
            throw new \yii\web\ServiceUnavailableHttpException('Регистрация временно отключена.');
        }

        if(!Yii::$app->user->isGuest) return $this->goHome();

        $model = new SignupForm();
        $recaptcha = new RecaptchaService();

        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $captchaPassed = true;

            if($recaptcha->isEnabledFor(RecaptchaService::SCOPE_REGISTER))
            {
                if(!$recaptcha->isConfigured())
                {
                    Yii::error('reCAPTCHA is enabled for registration but is not configured', 'recaptcha');
                    $model->addError('username', 'Защита от ботов временно недоступна.');
                    $captchaPassed = false;
                }
                elseif(!$recaptcha->verify(
                    Yii::$app->request->post('g-recaptcha-response'),
                    Yii::$app->request->userIP
                ))
                {
                    $model->addError('username', 'Подтвердите, что вы не робот.');
                    $captchaPassed = false;
                }
            }

            if($captchaPassed)
            {
                try
                {
                    $user = (new RegistrationService())->register(
                        $model->username,
                        $model->email,
                        $model->password
                    );

                    AuditService::log('user.register', 'user', (string)$user->id);
                    Yii::$app->session->setFlash(
                        'success',
                        'Аккаунт создан. Подтвердите email, после этого он станет доступен и в лаунчере.'
                    );
                    return $this->redirect(['login']);
                }
                catch(\Throwable $e)
                {
                    Yii::error($e);
                    $model->addError('username', 'Не удалось создать аккаунт.');
                }
            }
        }

        return $this->render('register', [
            'model' => $model,
            'recaptcha' => $recaptcha,
        ]);
    }

    public function actionResendVerification()
    {
        $model = new PasswordResetRequestForm();
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $user = User::findOne(['email' => mb_strtolower($model->email)]);
            if($user && $user->status === User::STATUS_PENDING)
            {
                $raw = (new TokenService())->issue((int)$user->id, UserToken::TYPE_EMAIL_VERIFY, 86400);
                $url = Yii::$app->params['appUrl'].'/verify-email?token='.rawurlencode($raw);
                try
                {
                    Yii::$app->mailer
                        ->compose('verify-email', ['user' => $user, 'url' => $url])
                        ->setTo($user->email)
                        ->setSubject('Подтверждение регистрации HiveTech')
                        ->send();
                }
                catch(\Throwable $e)
                {
                    Yii::error($e, 'mail');
                }
            }
            Yii::$app->session->setFlash('success', 'Если аккаунт ожидает подтверждения, новое письмо сформировано.');
            return $this->refresh();
        }
        return $this->render('resend-verification', ['model' => $model]);
    }

    public function actionVerifyEmail(string $token)
    {
        $user = (new RegistrationService())->verify($token);
        if(!$user)
        {
            Yii::$app->session->setFlash('error', 'Ссылка недействительна или истекла.');
            return $this->redirect(['login']);
        }
        Yii::$app->session->setFlash('success', 'Email подтверждён. Теперь можно входить на сайт и в HiveTech Launcher.');
        return $this->redirect(['login']);
    }

    public function actionLogout()
    {
        (new SessionTracker())->revokeCurrent();
        AuditService::log('web.logout');
        Yii::$app->user->logout();
        Yii::$app->session->destroy();
        return $this->redirect(['/']);
    }

    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $user = User::findOne(['email' => mb_strtolower($model->email)]);
            if($user)
            {
                $raw = (new TokenService())->issue((int)$user->id, UserToken::TYPE_PASSWORD_RESET, 3600);
                $url = Yii::$app->params['appUrl'].'/reset-password?token='.rawurlencode($raw);
                Yii::$app->mailer
                    ->compose('password-reset', ['user' => $user, 'url' => $url])
                    ->setTo($user->email)
                    ->setSubject('Восстановление пароля HiveTech')
                    ->send();
            }
            Yii::$app->session->setFlash('success', 'Если такой email зарегистрирован, мы отправили ссылку для сброса пароля.');
            return $this->refresh();
        }
        return $this->render('request-password-reset', ['model' => $model]);
    }

    public function actionResetPassword(string $token)
    {
        $hash = hash('sha256', $token);
        $tokenModel = UserToken::find()
            ->where([
                'token_hash' => $hash,
                'type' => UserToken::TYPE_PASSWORD_RESET,
                'used_at' => null,
            ])
            ->andWhere(['>', 'expires_at', date('Y-m-d H:i:s')])
            ->one();

        if(!$tokenModel)
        {
            throw new \yii\web\BadRequestHttpException('Ссылка недействительна или истекла.');
        }

        $model = new ResetPasswordForm();
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $user = $tokenModel->user;
            $user->setPassword($model->password);
            $user->save(false, ['password', 'accessToken', 'serverID']);
            $tokenModel->used_at = date('Y-m-d H:i:s');
            $tokenModel->save(false, ['used_at']);
            (new SessionTracker())->revokeAll((int)$user->id);
            AuditService::log('user.password_reset', 'user', (string)$user->id);
            Yii::$app->session->setFlash('success', 'Пароль изменён. Старые web/launcher-сессии сброшены.');
            return $this->redirect(['login']);
        }
        return $this->render('reset-password', ['model' => $model]);
    }
}
