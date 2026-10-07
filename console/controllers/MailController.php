<?php

declare(strict_types=1);

namespace console\controllers;

use common\models\User;
use common\models\UserToken;
use common\services\TokenService;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

final class MailController extends Controller
{
    public function actionTest(string $to): int
    {
        if(!filter_var($to, FILTER_VALIDATE_EMAIL))
        {
            $this->stderr("Invalid recipient email: {$to}\n");
            return ExitCode::USAGE;
        }

        if(!ht_env('SMTP_HOST'))
        {
            $this->stderr(
                "SMTP is not configured: SMTP_HOST is empty.\n"
            );
            return ExitCode::CONFIG;
        }

        try
        {
            $sent = Yii::$app->mailer
                ->compose('smtp-test')
                ->setTo($to)
                ->setSubject('HiveGrid SMTP test')
                ->send();

            if(!$sent)
            {
                $this->stderr("Mailer returned false.\n");
                return ExitCode::UNAVAILABLE;
            }

            $this->stdout("Test email sent to {$to}.\n");
            return ExitCode::OK;
        }
        catch(\Throwable $e)
        {
            Yii::error([
                'message' => 'SMTP test failed',
                'recipient' => $to,
                'error' => $e->getMessage(),
            ], 'mail');

            $this->stderr(
                "SMTP test failed: {$e->getMessage()}\n"
            );

            return ExitCode::UNAVAILABLE;
        }
    }

    public function actionResendVerifications(
        ?string $email = null,
        int $limit = 100
    ): int
    {
        if(!ht_env('SMTP_HOST'))
        {
            $this->stderr(
                "SMTP is not configured: SMTP_HOST is empty.\n"
            );
            return ExitCode::CONFIG;
        }

        $limit = max(1, min($limit, 1000));

        $query = User::find()
            ->where(['email_verified' => 0])
            ->andWhere(['<>', 'status', User::STATUS_BLOCKED])
            ->orderBy(['id' => SORT_ASC])
            ->limit($limit);

        if($email !== null)
        {
            $email = mb_strtolower(trim($email));

            if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $this->stderr("Invalid email: {$email}\n");
                return ExitCode::USAGE;
            }

            $query->andWhere(['email' => $email]);
        }

        $users = $query->all();

        if(!$users)
        {
            $this->stdout("No unverified accounts found.\n");
            return ExitCode::OK;
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach($users as $user)
        {
            $previousLanguage = Yii::$app->language;
            $language = in_array(
                (string)$user->language,
                ['ru', 'en', 'de'],
                true
            )
                ? (string)$user->language
                : 'ru';

            Yii::$app->language = $language;

            try
            {
                $raw = (new TokenService())->issue(
                    (int)$user->id,
                    UserToken::TYPE_EMAIL_VERIFY,
                    86400
                );

                $url = Yii::$app->params['appUrl']
                    . '/verify-email?token='
                    . rawurlencode($raw);

                $sent = Yii::$app->mailer
                    ->compose('verify-email', [
                        'user' => $user,
                        'url' => $url,
                    ])
                    ->setTo($user->email)
                    ->setSubject(
                        Yii::t(
                            'app',
                            'Подтверждение регистрации HiveTech'
                        )
                    )
                    ->send();

                if(!$sent)
                {
                    ++$failedCount;

                    Yii::error([
                        'message' => 'Verification resend returned false',
                        'userId' => (int)$user->id,
                        'email' => $user->email,
                    ], 'mail');

                    $this->stderr(
                        "FAILED {$user->id} {$user->email}: mailer returned false\n"
                    );

                    continue;
                }

                ++$sentCount;
                $this->stdout(
                    "SENT {$user->id} {$user->email}\n"
                );
            }
            catch(\Throwable $e)
            {
                ++$failedCount;

                Yii::error([
                    'message' => 'Verification resend failed',
                    'userId' => (int)$user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ], 'mail');

                $this->stderr(
                    "FAILED {$user->id} {$user->email}: {$e->getMessage()}\n"
                );
            }
            finally
            {
                Yii::$app->language = $previousLanguage;
            }
        }

        $this->stdout(
            "Done. Sent: {$sentCount}; failed: {$failedCount}.\n"
        );

        return $failedCount === 0
            ? ExitCode::OK
            : ExitCode::UNAVAILABLE;
    }
}
