<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    DeliveryQueue,
    ModerationAction,
    ModerationActionHistory,
    Server,
    User
};
use Yii;
use yii\web\ForbiddenHttpException;

final class ModerationService
{
    public function create(
        int $actorId,
        int $userId,
        string $type,
        string $scope,
        ?int $serverId,
        string $reason,
        ?string $internalComment,
        ?string $expiresAt
    ): ModerationAction
    {
        $this->assertCreatePermission($actorId, $type, $scope);
        (new RbacService())->assertCanModerate($actorId, $userId);

        $user = User::findOne($userId);

        if(!$user)
        {
            throw new \DomainException('Пользователь не найден.');
        }

        if(!in_array($type, [
            ModerationAction::TYPE_WARN,
            ModerationAction::TYPE_MUTE,
            ModerationAction::TYPE_BAN,
        ], true))
        {
            throw new \DomainException('Неизвестный тип наказания.');
        }

        if(!in_array($scope, [
            ModerationAction::SCOPE_SERVER,
            ModerationAction::SCOPE_GLOBAL,
        ], true))
        {
            throw new \DomainException('Неизвестная область наказания.');
        }

        $server = null;

        if($scope === ModerationAction::SCOPE_SERVER)
        {
            $server = $serverId ? Server::findOne($serverId) : null;

            if(!$server)
            {
                throw new \DomainException(
                    'Для серверного наказания необходимо выбрать сервер.'
                );
            }
        }
        else
        {
            $serverId = null;
        }

        $reason = trim($reason);

        if($reason === '')
        {
            throw new \DomainException('Укажите причину наказания.');
        }

        if($expiresAt !== null && strtotime($expiresAt) <= time())
        {
            throw new \DomainException(
                'Дата окончания должна быть в будущем.'
            );
        }

        if($type !== ModerationAction::TYPE_WARN)
        {
            $exists = ModerationAction::find()
                ->where([
                    'user_id' => $userId,
                    'type' => $type,
                    'scope' => $scope,
                    'server_id' => $serverId,
                    'status' => ModerationAction::STATUS_ACTIVE,
                ])
                ->exists();

            if($exists)
            {
                throw new \DomainException(
                    'У пользователя уже есть активное наказание такого типа в этой области.'
                );
            }
        }

        $transaction = Yii::$app->db->beginTransaction();

        try
        {
            $action = new ModerationAction([
                'user_id' => $userId,
                'server_id' => $serverId,
                'type' => $type,
                'scope' => $scope,
                'reason' => $reason,
                'internal_comment' => trim((string)$internalComment) ?: null,
                'starts_at' => date('Y-m-d H:i:s'),
                'expires_at' => $type === ModerationAction::TYPE_WARN
                    ? null
                    : $expiresAt,
                'status' => $type === ModerationAction::TYPE_WARN
                    ? ModerationAction::STATUS_RECORDED
                    : ModerationAction::STATUS_ACTIVE,
                'created_by' => $actorId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if(!$action->save())
            {
                throw new \RuntimeException(
                    'Не удалось сохранить наказание: '
                    . json_encode(
                        $action->errors,
                        JSON_UNESCAPED_UNICODE
                    )
                );
            }

            $delivery = $this->queueApply($action, $user, $server);

            $action->metadata_json = json_encode(
                $delivery,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
            $action->save(false, ['metadata_json']);

            $this->history($action, 'created', $actorId, [
                'type' => $type,
                'scope' => $scope,
                'serverId' => $serverId,
                'expiresAt' => $action->expires_at,
                'delivery' => $delivery,
            ]);

            AuditService::log(
                'admin.moderation.create',
                'moderation_action',
                (string)$action->id,
                [
                    'targetUserId' => $userId,
                    'type' => $type,
                    'scope' => $scope,
                    'serverId' => $serverId,
                    'expiresAt' => $action->expires_at,
                ]
            );

            $transaction->commit();

            return $action;
        }
        catch(\Throwable $e)
        {
            if($transaction->isActive)
            {
                $transaction->rollBack();
            }

            throw $e;
        }
    }

    public function revoke(
        int $actorId,
        int $actionId,
        string $reason
    ): ModerationAction
    {
        if(!Yii::$app->authManager->checkAccess(
            $actorId,
            'revokePunishment'
        ))
        {
            throw new ForbiddenHttpException();
        }

        $transaction = Yii::$app->db->beginTransaction();

        try
        {
            $action = $this->lock($actionId);

            if(!$action)
            {
                throw new \DomainException('Наказание не найдено.');
            }

            (new RbacService())->assertCanModerate(
                $actorId,
                (int)$action->user_id
            );

            if(!in_array($action->status, [
                ModerationAction::STATUS_ACTIVE,
                ModerationAction::STATUS_RECORDED,
            ], true))
            {
                throw new \DomainException(
                    'Это наказание уже завершено.'
                );
            }

            $wasActive =
                $action->status === ModerationAction::STATUS_ACTIVE;

            $action->status = ModerationAction::STATUS_REVOKED;
            $action->revoked_by = $actorId;
            $action->revoked_at = date('Y-m-d H:i:s');
            $action->revoke_reason =
                trim($reason) ?: 'Снято модератором';
            $action->save(false);

            $delivery = $wasActive
                ? $this->queueReverse($action)
                : ['queued' => 0, 'missing' => []];

            $this->history($action, 'revoked', $actorId, [
                'reason' => $action->revoke_reason,
                'delivery' => $delivery,
            ]);

            AuditService::log(
                'admin.moderation.revoke',
                'moderation_action',
                (string)$action->id,
                [
                    'targetUserId' => (int)$action->user_id,
                    'reason' => $action->revoke_reason,
                ]
            );

            $transaction->commit();

            return $action;
        }
        catch(\Throwable $e)
        {
            if($transaction->isActive)
            {
                $transaction->rollBack();
            }

            throw $e;
        }
    }

    public function expireDue(int $limit = 200): int
    {
        $ids = ModerationAction::find()
            ->select('id')
            ->where([
                'status' => ModerationAction::STATUS_ACTIVE,
            ])
            ->andWhere(['not', ['expires_at' => null]])
            ->andWhere([
                '<=',
                'expires_at',
                date('Y-m-d H:i:s'),
            ])
            ->orderBy([
                'expires_at' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->limit($limit)
            ->column();

        $count = 0;

        foreach($ids as $id)
        {
            if($this->expireOne((int)$id))
            {
                $count++;
            }
        }

        return $count;
    }

    private function expireOne(int $id): bool
    {
        $transaction = Yii::$app->db->beginTransaction();

        try
        {
            $action = $this->lock($id);

            if(
                !$action
                || $action->status
                    !== ModerationAction::STATUS_ACTIVE
                || $action->expires_at === null
                || strtotime((string)$action->expires_at) > time()
            )
            {
                $transaction->rollBack();

                return false;
            }

            $action->status =
                ModerationAction::STATUS_EXPIRED;
            $action->save(false, ['status']);

            $delivery = $this->queueReverse($action);

            $this->history($action, 'expired', null, [
                'delivery' => $delivery,
            ]);

            AuditService::log(
                'system.moderation.expire',
                'moderation_action',
                (string)$action->id,
                [
                    'targetUserId' => (int)$action->user_id,
                ]
            );

            $transaction->commit();

            return true;
        }
        catch(\Throwable $e)
        {
            if($transaction->isActive)
            {
                $transaction->rollBack();
            }

            throw $e;
        }
    }

    private function assertCreatePermission(
        int $actorId,
        string $type,
        string $scope
    ): void
    {
        $permission = match($type) {
            ModerationAction::TYPE_WARN => 'createWarn',
            ModerationAction::TYPE_MUTE => 'createMute',
            ModerationAction::TYPE_BAN => 'createBan',
            default => null,
        };

        if(
            !$permission
            || !Yii::$app->authManager->checkAccess(
                $actorId,
                $permission
            )
        )
        {
            throw new ForbiddenHttpException();
        }

        if(
            $scope === ModerationAction::SCOPE_GLOBAL
            && $type === ModerationAction::TYPE_BAN
            && !Yii::$app->authManager->checkAccess(
                $actorId,
                'createGlobalBan'
            )
        )
        {
            throw new ForbiddenHttpException();
        }

        if(
            $scope === ModerationAction::SCOPE_GLOBAL
            && $type === ModerationAction::TYPE_MUTE
            && !Yii::$app->authManager->checkAccess(
                $actorId,
                'createGlobalMute'
            )
        )
        {
            throw new ForbiddenHttpException();
        }
    }

    private function queueApply(
        ModerationAction $action,
        User $user,
        ?Server $server
    ): array
    {
        return $this->queueCommands(
            $action,
            $user,
            $this->targetServers($action, $server),
            false
        );
    }

    private function queueReverse(
        ModerationAction $action
    ): array
    {
        $user = User::findOne($action->user_id);

        if(!$user)
        {
            return [
                'queued' => 0,
                'missing' => ['user'],
            ];
        }

        $server = $action->server_id
            ? Server::findOne($action->server_id)
            : null;

        return $this->queueCommands(
            $action,
            $user,
            $this->targetServers($action, $server),
            true
        );
    }

    private function targetServers(
        ModerationAction $action,
        ?Server $server
    ): array
    {
        if(
            $action->scope
                === ModerationAction::SCOPE_GLOBAL
        )
        {
            return Server::find()
                ->where(['enabled' => 1])
                ->orderBy([
                    'sort' => SORT_ASC,
                    'id' => SORT_ASC,
                ])
                ->all();
        }

        return $server ? [$server] : [];
    }

    private function queueCommands(
        ModerationAction $action,
        User $user,
        array $servers,
        bool $reverse
    ): array
    {
        $queued = 0;
        $missing = [];

        foreach($servers as $server)
        {
            $field = $this->commandField(
                $action->type,
                $reverse
            );

            $template = $field
                ? trim((string)$server->{$field})
                : '';

            if($template === '')
            {
                $missing[] = [
                    'serverId' => (int)$server->id,
                    'server' => (string)$server->name,
                    'command' => $field,
                ];

                continue;
            }

            $queue = new DeliveryQueue([
                'purchase_id' => null,
                'user_id' => $user->id,
                'server_id' => $server->id,
                'delivery_type' => 'rcon_command',
                'payload' => $this->renderCommand(
                    $template,
                    $user,
                    $action
                ),
                'status' => 'pending',
                'attempts' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if(!$queue->save(false))
            {
                throw new \RuntimeException(
                    'Не удалось поставить команду модерации в очередь.'
                );
            }

            $queued++;
        }

        return [
            'queued' => $queued,
            'missing' => $missing,
        ];
    }

    private function commandField(
        string $type,
        bool $reverse
    ): ?string
    {
        return match([$type, $reverse]) {
            [ModerationAction::TYPE_BAN, false]
                => 'moderation_ban_command',
            [ModerationAction::TYPE_BAN, true]
                => 'moderation_unban_command',
            [ModerationAction::TYPE_MUTE, false]
                => 'moderation_mute_command',
            [ModerationAction::TYPE_MUTE, true]
                => 'moderation_unmute_command',
            [ModerationAction::TYPE_WARN, false]
                => 'moderation_warn_command',
            default => null,
        };
    }

    private function renderCommand(
        string $template,
        User $user,
        ModerationAction $action
    ): string
    {
        $reason = preg_replace(
            '/[\r\n]+/u',
            ' ',
            (string)$action->reason
        );

        $reason = trim(
            mb_substr((string)$reason, 0, 180)
        );

        return strtr($template, [
            '{username}' => (string)$user->username,
            '{uuid}' => (string)$user->uuid,
            '{reason}' => $reason,
            '{duration}' => $this->durationToken($action),
            '{expires_at}' =>
                $action->expires_at ?: 'permanent',
        ]);
    }

    private function durationToken(
        ModerationAction $action
    ): string
    {
        if($action->expires_at === null)
        {
            return 'permanent';
        }

        $seconds = max(
            60,
            strtotime((string)$action->expires_at)
                - strtotime((string)$action->starts_at)
        );

        if($seconds % 86400 === 0)
        {
            return (string)($seconds / 86400) . 'd';
        }

        if($seconds % 3600 === 0)
        {
            return (string)($seconds / 3600) . 'h';
        }

        return (string)(int)ceil($seconds / 60) . 'm';
    }

    private function history(
        ModerationAction $action,
        string $eventType,
        ?int $actorId,
        array $data = []
    ): void
    {
        $row = new ModerationActionHistory([
            'action_id' => $action->id,
            'event_type' => $eventType,
            'actor_id' => $actorId,
            'data_json' => $data
                ? json_encode(
                    $data,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                )
                : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if(!$row->save(false))
        {
            throw new \RuntimeException(
                'Не удалось сохранить историю модерации.'
            );
        }
    }

    private function lock(
        int $id
    ): ?ModerationAction
    {
        $row = Yii::$app->db
            ->createCommand(
                'SELECT id FROM {{%moderation_actions}} '
                . 'WHERE id=:id FOR UPDATE',
                [':id' => $id]
            )
            ->queryOne();

        return $row
            ? ModerationAction::findOne((int)$row['id'])
            : null;
    }
}
