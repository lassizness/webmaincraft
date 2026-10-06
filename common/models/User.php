<?php

declare(strict_types=1);

namespace common\models;

use common\services\GravitPasswordHasher;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/** @property int $id @property string $uuid @property string $username @property string $email @property string $password @property ?string $accessToken @property ?string $serverID @property ?int $hwidId @property string $status @property int $email_verified @property string $created_at @property string $updated_at @property ?string $last_login_at */
final class User extends ActiveRecord implements IdentityInterface
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_BLOCKED = 'blocked';

    public static function tableName(): string { return '{{%users}}'; }

    public function rules(): array
    {
        return [
            [['uuid', 'username', 'email', 'password'], 'required'],
            ['username', 'match', 'pattern' => '/^[A-Za-z0-9_]{3,16}$/'],
            ['email', 'email'],
            ['email', 'string', 'max' => 254],
            [['username', 'email', 'uuid'], 'unique'],
            ['status', 'in', 'range' => [self::STATUS_PENDING, self::STATUS_ACTIVE, self::STATUS_BLOCKED]],
            ['email_verified', 'boolean'],
        ];
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        return self::find()->where(['id' => $id, 'status' => self::STATUS_ACTIVE])->one();
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return null;
    }

    public static function findByLogin(string $login): ?self
    {
        return self::find()->where(['or', ['username' => $login], ['email' => $login]])->one();
    }

    public static function findActiveByUsername(string $username): ?self
    {
        return self::find()->where(['username' => $username, 'status' => self::STATUS_ACTIVE])->one();
    }

    public function getId(): int|string { return $this->id; }

    public function getAuthKey(): string
    {
        return hash('sha256', $this->uuid . '|' . $this->password);
    }

    public function validateAuthKey($authKey): bool
    {
        return hash_equals($this->getAuthKey(), (string) $authKey);
    }

    public function validatePassword(string $password): bool
    {
        return GravitPasswordHasher::verify($password, $this->password);
    }

    public function setPassword(string $password): void
    {
        $this->password = GravitPasswordHasher::hash($password);
        $this->accessToken = null;
        $this->serverID = null;
    }

    public function getDisplayStatus(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Активен',
            self::STATUS_PENDING => 'Ожидает подтверждения',
            self::STATUS_BLOCKED => 'Заблокирован',
            default => $this->status,
        };
    }
}
