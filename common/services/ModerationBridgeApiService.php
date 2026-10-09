<?php

declare(strict_types=1);

namespace common\services;

use common\models\ModerationAction;
use Yii;
use yii\db\Query;
use yii\db\Transaction;

final class ModerationBridgeApiService
{
    public const API_VERSION = 1;
    public const MAX_CHANGES_LIMIT = 500;

    public function snapshot(
        int $serverId
    ): array
    {
        $transaction = Yii::$app->db->beginTransaction(
            Transaction::REPEATABLE_READ
        );

        try
        {
            $revision = $this->currentRevision();

            $rows = $this->activeQuery($serverId)
                ->orderBy(['m.id' => SORT_ASC])
                ->all();

            $transaction->commit();

            return [
                'revision' => $revision,
                'actions' => array_map(
                    [$this, 'serializeAction'],
                    $rows
                ),
            ];
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

    public function changes(
        int $serverId,
        int $after,
        int $limit = 200
    ): array
    {
        $after = max(0, $after);
        $limit = max(
            1,
            min(
                self::MAX_CHANGES_LIMIT,
                $limit
            )
        );

        $transaction = Yii::$app->db->beginTransaction(
            Transaction::REPEATABLE_READ
        );

        try
        {
            $currentRevision =
                $this->currentRevision();

            $rows = (new Query())
                ->select([
                    'revision' => 'h.id',
                    'event_type' => 'h.event_type',
                    'action_id' => 'm.id',
                    'uuid' => 'u.uuid',
                    'username' => 'u.username',
                    'type' => 'm.type',
                    'scope' => 'm.scope',
                    'server_code' => 's.code',
                    'reason' => 'm.reason',
                    'starts_at' => 'm.starts_at',
                    'expires_at' => 'm.expires_at',
                    'status' => 'm.status',
                ])
                ->from([
                    'h' => '{{%moderation_action_history}}',
                ])
                ->innerJoin(
                    ['m' => '{{%moderation_actions}}'],
                    'm.id = h.action_id'
                )
                ->innerJoin(
                    ['u' => '{{%users}}'],
                    'u.id = m.user_id'
                )
                ->leftJoin(
                    ['s' => '{{%servers}}'],
                    's.id = m.server_id'
                )
                ->where([
                    'and',
                    ['>', 'h.id', $after],
                    ['<=', 'h.id', $currentRevision],
                    [
                        'm.type' => [
                            ModerationAction::TYPE_BAN,
                            ModerationAction::TYPE_MUTE,
                        ],
                    ],
                    [
                        'or',
                        [
                            'm.scope' =>
                                ModerationAction::SCOPE_GLOBAL,
                        ],
                        [
                            'm.server_id' => $serverId,
                        ],
                    ],
                ])
                ->orderBy(['h.id' => SORT_ASC])
                ->limit($limit)
                ->all();

            $nextRevision = $currentRevision;
            $hasMore = false;

            if(count($rows) === $limit)
            {
                $last = end($rows);
                $nextRevision =
                    (int)($last['revision'] ?? $after);
                $hasMore =
                    $nextRevision < $currentRevision;
            }

            $transaction->commit();

            return [
                'revision' => $nextRevision,
                'current_revision' => $currentRevision,
                'has_more' => $hasMore,
                'changes' => array_map(
                    [$this, 'serializeChange'],
                    $rows
                ),
            ];
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

    public function player(
        int $serverId,
        string $uuid
    ): array
    {
        $transaction = Yii::$app->db->beginTransaction(
            Transaction::REPEATABLE_READ
        );

        try
        {
            $revision = $this->currentRevision();

            $rows = $this->activeQuery($serverId)
                ->andWhere(['u.uuid' => $uuid])
                ->orderBy(['m.id' => SORT_ASC])
                ->all();

            $transaction->commit();

            return [
                'revision' => $revision,
                'actions' => array_map(
                    [$this, 'serializeAction'],
                    $rows
                ),
            ];
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

    public function currentRevision(): int
    {
        return (int)(new Query())
            ->from('{{%moderation_action_history}}')
            ->max('id');
    }

    private function activeQuery(
        int $serverId
    ): Query
    {
        return (new Query())
            ->select([
                'action_id' => 'm.id',
                'uuid' => 'u.uuid',
                'username' => 'u.username',
                'type' => 'm.type',
                'scope' => 'm.scope',
                'server_code' => 's.code',
                'reason' => 'm.reason',
                'starts_at' => 'm.starts_at',
                'expires_at' => 'm.expires_at',
                'status' => 'm.status',
            ])
            ->from([
                'm' => '{{%moderation_actions}}',
            ])
            ->innerJoin(
                ['u' => '{{%users}}'],
                'u.id = m.user_id'
            )
            ->leftJoin(
                ['s' => '{{%servers}}'],
                's.id = m.server_id'
            )
            ->where([
                'and',
                [
                    'm.status' =>
                        ModerationAction::STATUS_ACTIVE,
                ],
                [
                    '<=',
                    'm.starts_at',
                    date('Y-m-d H:i:s'),
                ],
                [
                    'm.type' => [
                        ModerationAction::TYPE_BAN,
                        ModerationAction::TYPE_MUTE,
                    ],
                ],
                [
                    'or',
                    ['m.expires_at' => null],
                    [
                        '>',
                        'm.expires_at',
                        date('Y-m-d H:i:s'),
                    ],
                ],
                [
                    'or',
                    [
                        'm.scope' =>
                            ModerationAction::SCOPE_GLOBAL,
                    ],
                    [
                        'm.server_id' => $serverId,
                    ],
                ],
            ]);
    }

    private function serializeAction(
        array $row
    ): array
    {
        return [
            'id' => (int)$row['action_id'],
            'uuid' => (string)$row['uuid'],
            'username' => (string)$row['username'],
            'type' => (string)$row['type'],
            'scope' => (string)$row['scope'],
            'server_code' =>
                $row['server_code'] !== null
                    ? (string)$row['server_code']
                    : null,
            'reason' => (string)$row['reason'],
            'starts_at' => $this->isoDate(
                $row['starts_at'] ?? null
            ),
            'expires_at' => $this->isoDate(
                $row['expires_at'] ?? null
            ),
            'status' => (string)$row['status'],
        ];
    }

    private function serializeChange(
        array $row
    ): array
    {
        $eventType =
            (string)$row['event_type'];

        $operation = match($eventType) {
            'created' => 'apply',
            'revoked', 'expired' => 'reverse',
            default => 'refresh',
        };

        return [
            'revision' => (int)$row['revision'],
            'operation' => $operation,
            'event_type' => $eventType,
        ] + $this->serializeAction($row);
    }

    private function isoDate(
        mixed $value
    ): ?string
    {
        if(!$value)
        {
            return null;
        }

        $timestamp = strtotime(
            (string)$value
        );

        return $timestamp
            ? date(DATE_ATOM, $timestamp)
            : null;
    }
}
