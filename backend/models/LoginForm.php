<?php

declare(strict_types=1);

namespace backend\models;

use common\models\User;
use yii\base\Model;

final class LoginForm extends Model
{
    public string $login = '';
    public string $password = '';
    public bool $rememberMe = false;

    public function rules(): array
    {
        return [
            [['login', 'password'], 'required'],
            ['rememberMe', 'boolean'],
        ];
    }

    public function login(): bool
    {
        $user = User::findByLogin($this->login);

        if(
            !$user
            || $user->status !== User::STATUS_ACTIVE
            || !$user->validatePassword($this->password)
        )
        {
            $this->addError('password', 'Неверные данные.');
            return false;
        }

        if(
            !\Yii::$app->authManager->checkAccess(
                (int)$user->id,
                'admin'
            )
        )
        {
            $this->addError(
                'password',
                'Нет доступа к админ-панели.'
            );
            return false;
        }

        return \Yii::$app->user->login(
            $user,
            $this->rememberMe ? 86400 : 0
        );
    }
}
