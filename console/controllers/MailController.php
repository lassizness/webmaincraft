<?php

declare(strict_types=1);

namespace console\controllers;

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
}
