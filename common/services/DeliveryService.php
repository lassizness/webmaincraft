<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    DeliveryQueue,
    ModerationAction,
    Server,
    User
};

final class DeliveryService
{
    public function process(
        DeliveryQueue $queue
    ): void
    {
        if(!$this->isModerationDeliveryCurrent($queue))
        {
            return;
        }

        $user = User::findOne(
            $queue->user_id
        );

        if(!$user)
        {
            throw new \RuntimeException(
                'User not found'
            );
        }

        if(
            $queue->delivery_type
                === 'none'
        )
        {
            return;
        }

        if(!in_array(
            $queue->delivery_type,
            [
                'rcon_command',
                'permission',
                'cosmetic',
            ],
            true
        ))
        {
            throw new \RuntimeException(
                'Unsupported delivery type'
            );
        }

        $server = Server::findOne(
            $queue->server_id
        );

        if(!$server)
        {
            throw new \RuntimeException(
                'Server not found'
            );
        }

        $prefix =
            $server->rcon_env_key
            ?: 'MAIN';

        $host = (string)ht_env(
            'MINECRAFT_RCON_'
            . $prefix
            . '_HOST',
            $server->host
        );

        $port = (int)ht_env(
            'MINECRAFT_RCON_'
            . $prefix
            . '_PORT',
            25575
        );

        $password = (string)ht_env(
            'MINECRAFT_RCON_'
            . $prefix
            . '_PASSWORD',
            ''
        );

        if($password === '')
        {
            throw new \RuntimeException(
                'RCON password is not configured'
            );
        }

        $command = strtr(
            (string)$queue->payload,
            [
                '{username}' => $user->username,
                '{uuid}' => $user->uuid,
            ]
        );

        (new RconClient(
            $host,
            $port,
            $password
        ))->command($command);
    }

    private function isModerationDeliveryCurrent(
        DeliveryQueue $queue
    ): bool
    {
        if(!$queue->moderation_action_id)
        {
            return true;
        }

        $action = ModerationAction::findOne(
            $queue->moderation_action_id
        );

        if(!$action)
        {
            return false;
        }

        $operation =
            (string)$queue->moderation_operation;

        if($operation === 'reverse')
        {
            return in_array(
                $action->status,
                [
                    ModerationAction::STATUS_EXPIRED,
                    ModerationAction::STATUS_REVOKED,
                ],
                true
            );
        }

        if($operation !== 'apply')
        {
            return false;
        }

        if(
            $action->type
                === ModerationAction::TYPE_WARN
        )
        {
            return $action->status
                === ModerationAction::STATUS_RECORDED;
        }

        if(
            $action->status
                !== ModerationAction::STATUS_ACTIVE
        )
        {
            return false;
        }

        if(
            $action->expires_at !== null
            && strtotime(
                (string)$action->expires_at
            ) <= time()
        )
        {
            return false;
        }

        return true;
    }
}
