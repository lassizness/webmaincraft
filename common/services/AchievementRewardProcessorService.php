<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    AchievementRewardDelivery,
    DeliveryQueue,
    PrivilegeGroup,
    Server,
    User,
    UserPermission
};
use Yii;
use yii\db\Query;

final class AchievementRewardProcessorService
{
    public const MAX_ATTEMPTS = 5;

    public function process(
        int $limit = 50
    ): array
    {
        $limit = max(1, min(500, $limit));
        $now = date('Y-m-d H:i:s');

        $ids = AchievementRewardDelivery::find()
            ->select('id')
            ->where([
                'status' => [
                    AchievementRewardDelivery::STATUS_PENDING,
                    AchievementRewardDelivery::STATUS_FAILED,
                ],
            ])
            ->andWhere([
                '<',
                'attempts',
                self::MAX_ATTEMPTS,
            ])
            ->andWhere([
                'or',
                ['available_at' => null],
                ['<=', 'available_at', $now],
            ])
            ->orderBy(['id' => SORT_ASC])
            ->limit($limit)
            ->column();

        $result = [
            'done' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        foreach($ids as $id)
        {
            $status = $this->processOne((int)$id);
            $result[$status]++;
        }

        return $result;
    }

    private function processOne(
        int $id
    ): string
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try
        {
            $locked = $db
                ->createCommand(
                    '
                    SELECT id
                    FROM {{%achievement_reward_deliveries}}
                    WHERE id = :id
                    FOR UPDATE
                    ',
                    [':id' => $id]
                )
                ->queryOne();

            if(!$locked)
            {
                $transaction->rollBack();
                return 'skipped';
            }

            $delivery =
                AchievementRewardDelivery::findOne($id);

            if(
                !$delivery
                || $delivery->status
                    === AchievementRewardDelivery::STATUS_DONE
                || (int)$delivery->attempts
                    >= self::MAX_ATTEMPTS
            )
            {
                $transaction->rollBack();
                return 'skipped';
            }

            $delivery->status =
                AchievementRewardDelivery::STATUS_PROCESSING;
            $delivery->attempts =
                (int)$delivery->attempts + 1;
            $delivery->last_error = null;
            $delivery->updated_at =
                date('Y-m-d H:i:s');
            $delivery->save(false);

            $this->deliver($delivery);

            $delivery->status =
                AchievementRewardDelivery::STATUS_DONE;
            $delivery->available_at = null;
            $delivery->last_error = null;
            $delivery->delivered_at =
                date('Y-m-d H:i:s');
            $delivery->updated_at =
                date('Y-m-d H:i:s');
            $delivery->save(false);

            $this->refreshUnlockStatus(
                (int)$delivery->unlock_id
            );

            $transaction->commit();

            return 'done';
        }
        catch(\Throwable $e)
        {
            if($transaction->isActive)
            {
                $transaction->rollBack();
            }

            $this->markFailed($id, $e);

            return 'failed';
        }
    }

    private function deliver(
        AchievementRewardDelivery $delivery
    ): void
    {
        $reward = $delivery->reward;
        $unlock = $delivery->unlock;

        if(
            !$reward
            || !$unlock
            || !$unlock->user
            || !$unlock->achievement
        )
        {
            throw new \RuntimeException(
                'Reward delivery relation is missing.'
            );
        }

        $payload = [];

        if(trim((string)$reward->payload_json) !== '')
        {
            $payload = json_decode(
                (string)$reward->payload_json,
                true
            );

            if(!is_array($payload))
            {
                throw new \RuntimeException(
                    'Invalid reward payload.'
                );
            }
        }

        $user = $unlock->user;

        match((string)$reward->reward_type) {
            'wallet' => $this->wallet(
                $user,
                $unlock,
                $payload
            ),
            'rcon' => $this->rcon(
                $user,
                $payload
            ),
            'permission' => $this->permission(
                $user,
                $payload
            ),
            'privilege' => $this->privilege(
                $user,
                $payload
            ),
            default => throw new \RuntimeException(
                'Unsupported reward type.'
            ),
        };
    }

    private function wallet(
        User $user,
        $unlock,
        array $payload
    ): void
    {
        $amount = (float)($payload['amount'] ?? 0);

        if(
            $amount <= 0
            || $amount > 1000000000
        )
        {
            throw new \RuntimeException(
                'Invalid wallet reward amount.'
            );
        }

        (new WalletService())->credit(
            (int)$user->id,
            $amount,
            'achievement_reward',
            'Достижение: '
                . (string)$unlock->achievement->title,
            'achievement_unlock',
            (int)$unlock->id
        );
    }

    private function rcon(
        User $user,
        array $payload
    ): void
    {
        $serverId = (int)($payload['server_id'] ?? 0);
        $command = trim(
            (string)($payload['command'] ?? '')
        );

        if(
            $serverId < 1
            || $command === ''
            || mb_strlen($command) > 512
        )
        {
            throw new \RuntimeException(
                'Invalid RCON reward payload.'
            );
        }

        $server = Server::findOne([
            'id' => $serverId,
            'enabled' => 1,
        ]);

        if(!$server)
        {
            throw new \RuntimeException(
                'Reward server is unavailable.'
            );
        }

        $queue = new DeliveryQueue([
            'purchase_id' => null,
            'user_id' => (int)$user->id,
            'server_id' => $serverId,
            'delivery_type' => 'rcon_command',
            'payload' => $command,
            'status' => 'pending',
            'attempts' => 0,
            'available_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'processed_at' => null,
        ]);

        if(!$queue->save(false))
        {
            throw new \RuntimeException(
                'Failed to enqueue RCON reward.'
            );
        }
    }

    private function permission(
        User $user,
        array $payload
    ): void
    {
        $name = trim(
            (string)($payload['name'] ?? '')
        );

        if(
            $name === ''
            || mb_strlen($name) > 100
        )
        {
            throw new \RuntimeException(
                'Invalid permission reward.'
            );
        }

        Yii::$app->db
            ->createCommand(
                '
                INSERT IGNORE INTO {{%user_permissions}}
                (uuid, name)
                VALUES (:uuid, :name)
                ',
                [
                    ':uuid' => (string)$user->uuid,
                    ':name' => $name,
                ]
            )
            ->execute();
    }

    private function privilege(
        User $user,
        array $payload
    ): void
    {
        $serverId = (int)($payload['server_id'] ?? 0);
        $groupId = (int)($payload['group_id'] ?? 0);
        $days = (int)($payload['days'] ?? 0);

        if(
            $serverId < 1
            || $groupId < 1
            || $days < 1
            || $days > 3650
        )
        {
            throw new \RuntimeException(
                'Invalid privilege reward.'
            );
        }

        $server = Server::findOne([
            'id' => $serverId,
            'enabled' => 1,
        ]);

        $group = PrivilegeGroup::findOne([
            'id' => $groupId,
            'enabled' => 1,
        ]);

        if(!$server || !$group)
        {
            throw new \RuntimeException(
                'Privilege reward target is unavailable.'
            );
        }

        (new PrivilegeService())->grantByAchievement(
            (int)$user->id,
            $serverId,
            $group,
            $days
        );
    }

    private function markFailed(
        int $id,
        \Throwable $error
    ): void
    {
        $delivery =
            AchievementRewardDelivery::findOne($id);

        if(!$delivery)
        {
            return;
        }

        $attempts =
            min(
                self::MAX_ATTEMPTS,
                (int)$delivery->attempts + 1
            );

        $delay = min(
            3600,
            60 * (2 ** max(0, $attempts - 1))
        );

        $delivery->status =
            AchievementRewardDelivery::STATUS_FAILED;
        $delivery->attempts = $attempts;
        $delivery->available_at =
            $attempts < self::MAX_ATTEMPTS
                ? date(
                    'Y-m-d H:i:s',
                    time() + $delay
                )
                : null;
        $delivery->last_error =
            mb_substr(
                $error->getMessage(),
                0,
                4000
            );
        $delivery->updated_at =
            date('Y-m-d H:i:s');
        $delivery->save(false);

        $this->refreshUnlockStatus(
            (int)$delivery->unlock_id
        );
    }

    private function refreshUnlockStatus(
        int $unlockId
    ): void
    {
        $rows = (new Query())
            ->from('{{%achievement_reward_deliveries}}')
            ->where(['unlock_id' => $unlockId])
            ->select([
                'status',
                'attempts',
                'last_error',
            ])
            ->all();

        if(!$rows)
        {
            return;
        }

        $allDone = true;
        $permanentFailure = false;
        $lastError = null;

        foreach($rows as $row)
        {
            if(
                (string)$row['status']
                !== AchievementRewardDelivery::STATUS_DONE
            )
            {
                $allDone = false;
            }

            if(
                (string)$row['status']
                    === AchievementRewardDelivery::STATUS_FAILED
                && (int)$row['attempts']
                    >= self::MAX_ATTEMPTS
            )
            {
                $permanentFailure = true;
                $lastError =
                    (string)($row['last_error'] ?? '');
            }
        }

        $status = $allDone
            ? 'done'
            : ($permanentFailure
                ? 'failed'
                : 'pending');

        Yii::$app->db
            ->createCommand()
            ->update(
                '{{%achievement_unlocks}}',
                [
                    'reward_status' => $status,
                    'reward_error' =>
                        $permanentFailure
                            ? mb_substr(
                                $lastError ?? '',
                                0,
                                4000
                            )
                            : null,
                ],
                ['id' => $unlockId]
            )
            ->execute();
    }
}
