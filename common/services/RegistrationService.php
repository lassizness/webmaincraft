<?php

declare(strict_types=1);

namespace common\services;

use common\models\User;
use common\models\UserToken;
use common\models\Wallet;
use Yii;

final class RegistrationService
{
    public function register(
        string $username,
        string $email,
        string $password
    ): User
    {
        $tx = Yii::$app->db->beginTransaction();

        try
        {
            $user = new User();
            $user->uuid = UuidService::v4();
            $user->username = $username;
            $user->email = mb_strtolower($email);
            $user->setPassword($password);
            $user->status = User::STATUS_ACTIVE;
            $user->email_verified = 0;
            $user->language = in_array(
                Yii::$app->language,
                ['ru', 'en', 'de'],
                true
            )
                ? Yii::$app->language
                : 'ru';

            if(!$user->save())
            {
                throw new \DomainException(
                    json_encode(
                        $user->errors,
                        JSON_UNESCAPED_UNICODE
                    )
                );
            }

            $wallet = new Wallet([
                'user_id' => $user->id,
                'balance' => 0,
            ]);

            if(!$wallet->save())
            {
                throw new \RuntimeException('Wallet create failed');
            }

            $role = Yii::$app->authManager->getRole('user');

            if($role)
            {
                Yii::$app->authManager->assign(
                    $role,
                    (int)$user->id
                );
            }

            $raw = (new TokenService())->issue(
                (int)$user->id,
                UserToken::TYPE_EMAIL_VERIFY,
                86400
            );

            $tx->commit();

            $url = Yii::$app->params['appUrl']
                . '/verify-email?token='
                . rawurlencode($raw);

            try
            {
                Yii::$app->mailer
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
            }
            catch(\Throwable $mailError)
            {
                Yii::error([
                    'message' => 'Failed to send registration verification email',
                    'userId' => (int)$user->id,
                    'email' => $user->email,
                    'error' => $mailError->getMessage(),
                ], 'mail');
            }

            return $user;
        }
        catch(\Throwable $e)
        {
            if($tx->isActive)
            {
                $tx->rollBack();
            }

            throw $e;
        }
    }

    public function verify(string $token): ?User
    {
        $tokenModel = (new TokenService())->consume(
            $token,
            UserToken::TYPE_EMAIL_VERIFY
        );

        if(!$tokenModel)
        {
            return null;
        }

        $user = $tokenModel->user;

        if(!$user)
        {
            return null;
        }

        $user->email_verified = 1;
        $user->save(false, ['email_verified']);

        return $user;
    }
}
