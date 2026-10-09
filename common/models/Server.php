<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class Server extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%servers}}';
    }

    public function getModerationBridge()
    {
        return $this->hasOne(
            ServerModerationBridge::class,
            ['server_id' => 'id']
        );
    }

    public function rules(): array
    {
        return [
            [['name', 'code', 'host', 'port'], 'required'],
            [['port', 'enabled', 'sort'], 'integer'],
            [[
                'name',
                'code',
                'host',
                'rcon_env_key',
                'description',
            ], 'string', 'max' => 255],
            [[
                'moderation_ban_command',
                'moderation_unban_command',
                'moderation_mute_command',
                'moderation_unmute_command',
                'moderation_warn_command',
            ], 'string', 'max' => 512],
            ['code', 'unique'],
        ];
    }
}
