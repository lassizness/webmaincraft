<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    ServerTelemetryStatus,
    User
};
use Yii;
use yii\db\Transaction;

final class TelemetryApiService
{
    public const API_VERSION = 1;
    public const MAX_BATCH_SIZE = 500;
    public const MAX_METADATA_BYTES = 8192;
    public const MAX_OBJECT_LENGTH = 128;

    public function ingest(
        int $serverId,
        array $payload,
        ?string $ipAddress = null
    ): array
    {
        $events = $payload['events'] ?? null;

        if(!is_array($events))
        {
            throw new \InvalidArgumentException(
                'events_must_be_array'
            );
        }

        $count = count($events);

        if(
            $count < 1
            || $count > self::MAX_BATCH_SIZE
        )
        {
            throw new \InvalidArgumentException(
                'invalid_batch_size'
            );
        }

        $normalized = [];

        foreach($events as $index => $event)
        {
            if(!is_array($event))
            {
                throw new \InvalidArgumentException(
                    'invalid_event_' . $index
                );
            }

            $normalized[] =
                $this->normalizeEvent(
                    $event,
                    (int)$index
                );
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction(
            Transaction::READ_COMMITTED
        );

        try
        {
            $accepted = 0;
            $duplicates = 0;
            $unresolved = 0;
            $changedUsers = [];
            $userCache = [];
            $latestEventAt = null;

            foreach($normalized as $event)
            {
                $user = $this->resolveUser(
                    $event,
                    $userCache
                );

                if(
                    !$user
                    && (
                        $event['username'] !== null
                        || $event['player_uuid'] !== null
                    )
                )
                {
                    $unresolved++;
                }

                $inserted = $db
                    ->createCommand(
                        '
                        INSERT IGNORE INTO {{%telemetry_events}}
                        (
                            event_uuid,
                            server_id,
                            user_id,
                            player_uuid,
                            username,
                            session_uuid,
                            event_type,
                            source,
                            object_id,
                            amount,
                            metadata_json,
                            occurred_at,
                            received_at
                        )
                        VALUES
                        (
                            :event_uuid,
                            :server_id,
                            :user_id,
                            :player_uuid,
                            :username,
                            :session_uuid,
                            :event_type,
                            :source,
                            :object_id,
                            :amount,
                            :metadata_json,
                            :occurred_at,
                            :received_at
                        )
                        ',
                        [
                            ':event_uuid' =>
                                $event['id'],
                            ':server_id' =>
                                $serverId,
                            ':user_id' =>
                                $user
                                    ? (int)$user->id
                                    : null,
                            ':player_uuid' =>
                                $event['player_uuid'],
                            ':username' =>
                                $event['username'],
                            ':session_uuid' =>
                                $event['session_id'],
                            ':event_type' =>
                                $event['type'],
                            ':source' =>
                                $event['source'],
                            ':object_id' =>
                                $event['object'],
                            ':amount' =>
                                $event['amount'],
                            ':metadata_json' =>
                                $event['metadata_json'],
                            ':occurred_at' =>
                                $event['occurred_at'],
                            ':received_at' =>
                                date('Y-m-d H:i:s'),
                        ]
                    )
                    ->execute();

                if($inserted < 1)
                {
                    $duplicates++;
                    continue;
                }

                $accepted++;

                if(
                    $latestEventAt === null
                    || $event['occurred_at']
                        > $latestEventAt
                )
                {
                    $latestEventAt =
                        $event['occurred_at'];
                }

                if(!$user)
                {
                    continue;
                }

                $userId = (int)$user->id;
                $changedUsers[$userId] = true;

                $this->updateSession(
                    $serverId,
                    $userId,
                    $event
                );

                foreach(
                    $this->statIncrements($event)
                    as $statKey => $amount
                )
                {
                    $this->incrementStat(
                        $serverId,
                        $userId,
                        $statKey,
                        $amount,
                        $event['occurred_at']
                    );
                }

                $this->recordDiscovery(
                    $serverId,
                    $userId,
                    $event
                );
            }

            $unlocked = [];

            $evaluator =
                new AchievementEvaluatorService();

            foreach(array_keys($changedUsers) as $userId)
            {
                foreach(
                    $evaluator->evaluateUser(
                        (int)$userId
                    )
                    as $achievement
                )
                {
                    $unlocked[] = [
                        'user_id' => (int)$userId,
                    ] + $achievement;
                }
            }

            $this->touchBatchStatus(
                $serverId,
                $accepted,
                $count,
                $latestEventAt,
                $ipAddress
            );

            $transaction->commit();

            return [
                'accepted' => $accepted,
                'duplicates' => $duplicates,
                'unresolved_players' => $unresolved,
                'received' => $count,
                'achievements_unlocked' => $unlocked,
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

    public function heartbeat(
        int $serverId,
        array $payload,
        ?string $ipAddress = null
    ): array
    {
        foreach([
            'telemetry_version',
            'minecraft_version',
            'online_players',
            'queue_size',
        ] as $required)
        {
            if(!array_key_exists($required, $payload))
            {
                throw new \InvalidArgumentException(
                    'missing_' . $required
                );
            }
        }

        $version = $this->limitedString(
            $payload['telemetry_version'],
            64
        );

        $minecraft = $this->limitedString(
            $payload['minecraft_version'],
            32
        );

        if(
            $version === null
            || $minecraft === null
        )
        {
            throw new \InvalidArgumentException(
                'invalid_heartbeat_version'
            );
        }

        $onlinePlayers = $this->nonNegativeInt(
            $payload['online_players'],
            'online_players'
        );

        $queueSize = $this->nonNegativeInt(
            $payload['queue_size'],
            'queue_size'
        );

        $bufferedEvents = $this->nonNegativeInt(
            $payload['buffered_events'] ?? 0,
            'buffered_events'
        );

        $forge = $this->limitedString(
            $payload['forge_version'] ?? null,
            64
        );

        $lastError = trim(
            (string)($payload['last_error'] ?? '')
        );

        if(mb_strlen($lastError) > 4000)
        {
            $lastError = mb_substr(
                $lastError,
                0,
                4000
            );
        }

        $status = $this->status($serverId);
        $now = date('Y-m-d H:i:s');

        $status->telemetry_version = $version;
        $status->minecraft_version = $minecraft;
        $status->forge_version = $forge;
        $status->online_players = $onlinePlayers;
        $status->queue_size = $queueSize;
        $status->buffered_events = $bufferedEvents;
        $status->last_heartbeat_at = $now;
        $status->last_seen_ip = $ipAddress;
        $status->last_error =
            $lastError !== ''
                ? $lastError
                : null;
        $status->updated_at = $now;
        $status->save(false);

        return [
            'accepted' => true,
            'max_batch_size' =>
                self::MAX_BATCH_SIZE,
            'server_time' => date(DATE_ATOM),
        ];
    }

    private function normalizeEvent(
        array $event,
        int $index
    ): array
    {
        $id = strtolower(
            trim((string)($event['id'] ?? ''))
        );

        if(!$this->isUuid($id))
        {
            throw new \InvalidArgumentException(
                'invalid_event_id_' . $index
            );
        }

        $sessionId = trim(
            (string)($event['session_id'] ?? '')
        );

        if(
            $sessionId !== ''
            && !$this->isUuid($sessionId)
        )
        {
            throw new \InvalidArgumentException(
                'invalid_session_id_' . $index
            );
        }

        $playerUuid = strtolower(
            trim((string)($event['player_uuid'] ?? ''))
        );

        if(
            $playerUuid !== ''
            && !$this->isUuid($playerUuid)
        )
        {
            throw new \InvalidArgumentException(
                'invalid_player_uuid_' . $index
            );
        }

        $username = trim(
            (string)($event['username'] ?? '')
        );

        if(
            $username !== ''
            && !preg_match(
                '/^[A-Za-z0-9_]{3,16}$/',
                $username
            )
        )
        {
            throw new \InvalidArgumentException(
                'invalid_username_' . $index
            );
        }

        $type = strtolower(
            trim((string)($event['type'] ?? ''))
        );

        if(
            !preg_match(
                '/^[a-z0-9][a-z0-9_.-]{1,63}$/',
                $type
            )
        )
        {
            throw new \InvalidArgumentException(
                'invalid_event_type_' . $index
            );
        }

        $source = strtolower(
            trim((string)($event['source'] ?? 'forge'))
        );

        if(
            !preg_match(
                '/^[a-z0-9][a-z0-9_.-]{1,31}$/',
                $source
            )
        )
        {
            throw new \InvalidArgumentException(
                'invalid_event_source_' . $index
            );
        }

        $object = trim(
            (string)($event['object'] ?? '')
        );

        if(
            $object !== ''
            && (
                mb_strlen($object)
                    > self::MAX_OBJECT_LENGTH
                || preg_match(
                    '/[\x00-\x1F\x7F]/u',
                    $object
                )
            )
        )
        {
            throw new \InvalidArgumentException(
                'invalid_event_object_' . $index
            );
        }

        $amount = filter_var(
            $event['amount'] ?? 1,
            FILTER_VALIDATE_INT
        );

        if(
            $amount === false
            || $amount < 1
            || $amount > 1000000000
        )
        {
            throw new \InvalidArgumentException(
                'invalid_event_amount_' . $index
            );
        }

        $occurredAt = $this->normalizeTime(
            $event['occurred_at'] ?? null,
            $index
        );

        $metadata = $event['metadata'] ?? null;

        if(
            $metadata !== null
            && !is_array($metadata)
        )
        {
            throw new \InvalidArgumentException(
                'invalid_metadata_' . $index
            );
        }

        $metadataJson = $metadata !== null
            ? json_encode(
                $metadata,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            )
            : null;

        if(
            $metadataJson === false
            || (
                $metadataJson !== null
                && strlen($metadataJson)
                    > self::MAX_METADATA_BYTES
            )
        )
        {
            throw new \InvalidArgumentException(
                'metadata_too_large_' . $index
            );
        }

        return [
            'id' => $id,
            'session_id' =>
                $sessionId !== ''
                    ? strtolower($sessionId)
                    : null,
            'player_uuid' =>
                $playerUuid !== ''
                    ? $playerUuid
                    : null,
            'username' =>
                $username !== ''
                    ? $username
                    : null,
            'type' => $type,
            'source' => $source,
            'object' =>
                $object !== ''
                    ? $object
                    : null,
            'amount' => (int)$amount,
            'metadata_json' => $metadataJson,
            'occurred_at' => $occurredAt,
        ];
    }

    private function normalizeTime(
        mixed $value,
        int $index
    ): string
    {
        if($value === null || $value === '')
        {
            return date('Y-m-d H:i:s');
        }

        if(is_int($value) || ctype_digit((string)$value))
        {
            $timestamp = (int)$value;

            if($timestamp > 20000000000)
            {
                $timestamp = intdiv(
                    $timestamp,
                    1000
                );
            }
        }
        else
        {
            $timestamp = strtotime(
                (string)$value
            );

            if($timestamp === false)
            {
                throw new \InvalidArgumentException(
                    'invalid_occurred_at_' . $index
                );
            }
        }

        if(
            $timestamp < 946684800
            || $timestamp > time() + 300
        )
        {
            throw new \InvalidArgumentException(
                'invalid_occurred_at_' . $index
            );
        }

        return date(
            'Y-m-d H:i:s',
            $timestamp
        );
    }

    private function resolveUser(
        array $event,
        array &$cache
    ): ?User
    {
        $cacheKey =
            strtolower(
                (string)($event['username'] ?? '')
            )
            . '|'
            . strtolower(
                (string)($event['player_uuid'] ?? '')
            );

        if(array_key_exists($cacheKey, $cache))
        {
            return $cache[$cacheKey];
        }

        $user = null;

        if($event['username'] !== null)
        {
            $user = User::find()
                ->where([
                    'username' => $event['username'],
                    'status' => User::STATUS_ACTIVE,
                ])
                ->one();
        }

        if(
            !$user
            && $event['player_uuid'] !== null
        )
        {
            $user = User::find()
                ->where([
                    'uuid' => $event['player_uuid'],
                    'status' => User::STATUS_ACTIVE,
                ])
                ->one();
        }

        $cache[$cacheKey] = $user;

        return $user;
    }

    private function statIncrements(
        array $event
    ): array
    {
        $type = (string)$event['type'];
        $amount = (int)$event['amount'];
        $result = [];

        if($type === 'player.playtime')
        {
            $result['playtime_seconds'] =
                $amount;

            return $result;
        }

        $result[$type . '.total'] = $amount;

        $alias = match($type) {
            'block.break' => 'blocks.break.total',
            'block.place' => 'blocks.place.total',
            default => null,
        };

        if($alias !== null)
        {
            $result[$alias] =
                ($result[$alias] ?? 0)
                + $amount;
        }

        if($event['object'] !== null)
        {
            $key = $type
                . '.'
                . (string)$event['object'];

            if(mb_strlen($key) <= 191)
            {
                $result[$key] =
                    ($result[$key] ?? 0)
                    + $amount;
            }
        }

        return $result;
    }

    private function incrementStat(
        int $serverId,
        int $userId,
        string $statKey,
        int $amount,
        string $updatedAt
    ): void
    {
        Yii::$app->db
            ->createCommand(
                '
                INSERT INTO {{%player_stats}}
                (
                    user_id,
                    server_id,
                    stat_key,
                    stat_value,
                    updated_at
                )
                VALUES
                (
                    :user_id,
                    :server_id,
                    :stat_key,
                    :stat_value,
                    :updated_at
                )
                ON DUPLICATE KEY UPDATE
                    stat_value =
                        stat_value
                        + VALUES(stat_value),
                    updated_at =
                        GREATEST(
                            updated_at,
                            VALUES(updated_at)
                        )
                ',
                [
                    ':user_id' => $userId,
                    ':server_id' => $serverId,
                    ':stat_key' => $statKey,
                    ':stat_value' => $amount,
                    ':updated_at' => $updatedAt,
                ]
            )
            ->execute();
    }

    private function updateSession(
        int $serverId,
        int $userId,
        array $event
    ): void
    {
        if($event['session_id'] === null)
        {
            return;
        }

        Yii::$app->db
            ->createCommand(
                '
                INSERT INTO {{%player_sessions}}
                (
                    session_uuid,
                    user_id,
                    server_id,
                    started_at,
                    ended_at,
                    duration_seconds,
                    last_event_at
                )
                VALUES
                (
                    :session_uuid,
                    :user_id,
                    :server_id,
                    :started_at,
                    NULL,
                    0,
                    :last_event_at
                )
                ON DUPLICATE KEY UPDATE
                    started_at =
                        LEAST(
                            started_at,
                            VALUES(started_at)
                        ),
                    last_event_at =
                        GREATEST(
                            last_event_at,
                            VALUES(last_event_at)
                        )
                ',
                [
                    ':session_uuid' =>
                        $event['session_id'],
                    ':user_id' =>
                        $userId,
                    ':server_id' =>
                        $serverId,
                    ':started_at' =>
                        $event['occurred_at'],
                    ':last_event_at' =>
                        $event['occurred_at'],
                ]
            )
            ->execute();

        if($event['type'] !== 'player.logout')
        {
            return;
        }

        Yii::$app->db
            ->createCommand(
                '
                UPDATE {{%player_sessions}}
                SET
                    ended_at = :ended_at,
                    last_event_at =
                        GREATEST(
                            last_event_at,
                            :last_event_at
                        ),
                    duration_seconds =
                        GREATEST(
                            0,
                            TIMESTAMPDIFF(
                                SECOND,
                                started_at,
                                :ended_at_duration
                            )
                        )
                WHERE session_uuid = :session_uuid
                  AND user_id = :user_id
                  AND server_id = :server_id
                ',
                [
                    ':ended_at' =>
                        $event['occurred_at'],
                    ':last_event_at' =>
                        $event['occurred_at'],
                    ':ended_at_duration' =>
                        $event['occurred_at'],
                    ':session_uuid' =>
                        $event['session_id'],
                    ':user_id' =>
                        $userId,
                    ':server_id' =>
                        $serverId,
                ]
            )
            ->execute();
    }

    private function recordDiscovery(
        int $serverId,
        int $userId,
        array $event
    ): void
    {
        $discoveryType = null;
        $counterKey = null;

        if(
            $event['type'] === 'dimension.enter'
            && $event['object'] !== null
        )
        {
            $discoveryType = 'dimension';
            $counterKey = 'dimensions.visited';
        }
        elseif(
            str_ends_with(
                (string)$event['type'],
                '.discovered'
            )
            && $event['object'] !== null
        )
        {
            $discoveryType =
                (string)$event['type'];

            $counterKey =
                (string)$event['type']
                . '.unique';
        }

        if(
            $discoveryType === null
            || $counterKey === null
        )
        {
            return;
        }

        $inserted = Yii::$app->db
            ->createCommand(
                '
                INSERT IGNORE INTO {{%player_discoveries}}
                (
                    user_id,
                    server_id,
                    discovery_type,
                    object_id,
                    metadata_json,
                    first_seen_at
                )
                VALUES
                (
                    :user_id,
                    :server_id,
                    :discovery_type,
                    :object_id,
                    :metadata_json,
                    :first_seen_at
                )
                ',
                [
                    ':user_id' => $userId,
                    ':server_id' => $serverId,
                    ':discovery_type' => $discoveryType,
                    ':object_id' => $event['object'],
                    ':metadata_json' => $event['metadata_json'],
                    ':first_seen_at' => $event['occurred_at'],
                ]
            )
            ->execute();

        if($inserted > 0)
        {
            $this->incrementStat(
                $serverId,
                $userId,
                $counterKey,
                1,
                $event['occurred_at']
            );
        }
    }

    private function touchBatchStatus(
        int $serverId,
        int $accepted,
        int $batchSize,
        ?string $lastEventAt,
        ?string $ipAddress
    ): void
    {
        $status = $this->status($serverId);
        $now = date('Y-m-d H:i:s');

        $status->events_received_total =
            (int)$status->events_received_total
            + $accepted;
        $status->last_batch_size = $batchSize;
        $status->last_batch_at = $now;
        $status->last_seen_ip = $ipAddress;
        $status->updated_at = $now;

        if($lastEventAt !== null)
        {
            $status->last_event_at =
                !$status->last_event_at
                || $lastEventAt
                    > (string)$status->last_event_at
                    ? $lastEventAt
                    : $status->last_event_at;
        }

        $status->save(false);
    }

    private function status(
        int $serverId
    ): ServerTelemetryStatus
    {
        $status = ServerTelemetryStatus::findOne([
            'server_id' => $serverId,
        ]);

        if($status)
        {
            return $status;
        }

        $now = date('Y-m-d H:i:s');

        $status = new ServerTelemetryStatus([
            'server_id' => $serverId,
            'online_players' => 0,
            'queue_size' => 0,
            'buffered_events' => 0,
            'events_received_total' => 0,
            'last_batch_size' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $status->save(false);

        return $status;
    }

    private function nonNegativeInt(
        mixed $value,
        string $name
    ): int
    {
        $parsed = filter_var(
            $value,
            FILTER_VALIDATE_INT
        );

        if(
            $parsed === false
            || $parsed < 0
        )
        {
            throw new \InvalidArgumentException(
                'invalid_' . $name
            );
        }

        return (int)$parsed;
    }

    private function limitedString(
        mixed $value,
        int $maxLength
    ): ?string
    {
        $value = trim((string)$value);

        if($value === '')
        {
            return null;
        }

        return mb_substr(
            $value,
            0,
            $maxLength
        );
    }

    private function isUuid(
        string $value
    ): bool
    {
        return (bool)preg_match(
            '/^[0-9a-f]{8}-'
            . '[0-9a-f]{4}-'
            . '[0-9a-f]{4}-'
            . '[0-9a-f]{4}-'
            . '[0-9a-f]{12}$/',
            strtolower($value)
        );
    }
}
