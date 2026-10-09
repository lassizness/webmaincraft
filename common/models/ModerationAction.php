<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class ModerationAction extends ActiveRecord
{
    public const TYPE_WARN = 'warn';
    public const TYPE_MUTE = 'mute';
    public const TYPE_BAN = 'ban';

    public const SCOPE_SERVER = 'server';
    public const SCOPE_GLOBAL = 'global';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_RECORDED = 'recorded';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVOKED = 'revoked';

    public static function tableName(): string
    {
        return '{{%moderation_actions}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'type', 'scope', 'reason', 'starts_at', 'status', 'created_at'], 'required'],
            [['user_id', 'server_id', 'created_by', 'revoked_by'], 'integer'],
            [['reason', 'internal_comment', 'revoke_reason', 'metadata_json'], 'string'],
            [['type'], 'in', 'range' => [
                self::TYPE_WARN,
                self::TYPE_MUTE,
                self::TYPE_BAN,
            ]],
            [['scope'], 'in', 'range' => [
                self::SCOPE_SERVER,
                self::SCOPE_GLOBAL,
            ]],
            [['status'], 'in', 'range' => [
                self::STATUS_ACTIVE,
                self::STATUS_RECORDED,
                self::STATUS_EXPIRED,
                self::STATUS_REVOKED,
            ]],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getServer()
    {
        return $this->hasOne(Server::class, ['id' => 'server_id']);
    }

    public function getCreator()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getRevoker()
    {
        return $this->hasOne(User::class, ['id' => 'revoked_by']);
    }

    public function getHistory()
    {
        return $this->hasMany(ModerationActionHistory::class, ['action_id' => 'id'])
            ->orderBy(['id' => SORT_ASC]);
    }

    public function isPermanent(): bool
    {
        return $this->expires_at === null;
    }
}
