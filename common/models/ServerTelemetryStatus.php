<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class ServerTelemetryStatus extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%server_telemetry_status}}';
    }

    public function getServer()
    {
        return $this->hasOne(
            Server::class,
            ['id' => 'server_id']
        );
    }

    public function isOnline(
        int $maxAgeSeconds = 120
    ): bool
    {
        if(!$this->last_heartbeat_at)
        {
            return false;
        }

        return strtotime(
            (string)$this->last_heartbeat_at
        ) >= time() - $maxAgeSeconds;
    }
}
