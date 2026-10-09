<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class ServerModerationBridge extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%server_moderation_bridge}}';
    }

    public function rules(): array
    {
        return [
            [['server_id', 'token_hash'], 'required'],
            [[
                'server_id',
                'enabled',
                'last_revision',
                'cached_actions',
                'online_players',
            ], 'integer'],
            [['last_error'], 'string'],
            [['token_hash'], 'string', 'max' => 64],
            [['token_hint'], 'string', 'max' => 24],
            [['last_seen_ip'], 'string', 'max' => 45],
            [['bridge_version', 'forge_version'], 'string', 'max' => 64],
            [['minecraft_version'], 'string', 'max' => 32],
        ];
    }

    public function getServer()
    {
        return $this->hasOne(Server::class, ['id' => 'server_id']);
    }

    public function isOnline(int $maxAgeSeconds = 120): bool
    {
        if(!$this->last_heartbeat_at)
        {
            return false;
        }

        return strtotime((string)$this->last_heartbeat_at)
            >= time() - $maxAgeSeconds;
    }
}
