<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    DeliveryQueue,
    Server,
    User
};

final class MinecraftOperatorService
{
    public const OPERATION_GRANT = 'grant';
    public const OPERATION_REVOKE = 'revoke';

    public function enqueue(
        User $user,
        Server $server,
        string $operation
    ): DeliveryQueue
    {
        if(!(bool)$server->enabled)
        {
            throw new \InvalidArgumentException(
                'Minecraft server is disabled'
            );
        }

        $payload = match($operation)
        {
            self::OPERATION_GRANT => 'op {username}',
            self::OPERATION_REVOKE => 'deop {username}',
            default => throw new \InvalidArgumentException(
                'Unsupported operator operation'
            ),
        };

        $queue = new DeliveryQueue([
            'purchase_id' => null,
            'user_id' => (int)$user->id,
            'server_id' => (int)$server->id,
            'delivery_type' => 'rcon_command',
            'payload' => $payload,
            'status' => 'pending',
            'attempts' => 0,
            'available_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'processed_at' => null,
        ]);

        if(!$queue->save(false))
        {
            throw new \RuntimeException(
                'Failed to enqueue Minecraft operator command'
            );
        }

        return $queue;
    }

    public static function payloads(): array
    {
        return [
            'op {username}',
            'deop {username}',
        ];
    }
}
