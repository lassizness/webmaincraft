<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    DeliveryQueue,
    PrivilegeGroup,
    PrivilegeHistory,
    ShopProduct,
    ShopPurchase,
    UserPrivilege
};
use Yii;

final class PrivilegeService
{
    public function purchase(
        int $userId,
        ShopProduct $product,
        ShopPurchase $purchase
    ): UserPrivilege
    {
        if(
            $product->product_type
            !== ShopProduct::TYPE_PRIVILEGE
        )
        {
            throw new \DomainException(
                'Товар не является привилегией.'
            );
        }

        if(!$product->server_id)
        {
            throw new \DomainException(
                'Для привилегии не выбран сервер.'
            );
        }

        if(!$product->privilege_group_id)
        {
            throw new \DomainException(
                'Для товара не выбран статус.'
            );
        }

        if((int)$product->duration_days < 1)
        {
            throw new \DomainException(
                'Для товара не задан срок действия.'
            );
        }

        $group =
            $product->privilegeGroup;

        if(
            !$group
            || !$group->enabled
        )
        {
            throw new \DomainException(
                'Статус недоступен.'
            );
        }

        return $this->grant(
            $userId,
            (int)$product->server_id,
            $group,
            (int)$product->duration_days,
            'purchase',
            (int)$purchase->id,
            false
        );
    }

    public function grantByAdmin(
        int $userId,
        int $serverId,
        PrivilegeGroup $group,
        int $days
    ): UserPrivilege
    {
        return $this->grant(
            $userId,
            $serverId,
            $group,
            $days,
            'admin',
            null,
            true
        );
    }

    public function revoke(
        int $privilegeId,
        string $source = 'admin'
    ): void
    {
        $this->transaction(
            function() use(
                $privilegeId,
                $source
            ): void
            {
                $row =
                    $this->lockById(
                        $privilegeId
                    );

                if(!$row)
                {
                    throw new \DomainException(
                        'Привилегия не найдена.'
                    );
                }

                if(
                    $row->status
                    !== UserPrivilege::STATUS_ACTIVE
                )
                {
                    return;
                }

                $group =
                    $row->group;

                $oldActiveTo =
                    (string)$row->active_to;

                $row->status =
                    UserPrivilege::STATUS_REVOKED;

                $row->updated_at =
                    date(
                        'Y-m-d H:i:s'
                    );

                $row->save(
                    false
                );

                if($group)
                {
                    $this->queueRank(
                        $row,
                        'remove',
                        $group->ftbu_rank
                    );
                }

                (new CosmeticService())
                    ->resetForPrivilege(
                        $row
                    );

                $this->history(
                    $row,
                    'revoke',
                    $group?->id,
                    null,
                    $oldActiveTo,
                    null,
                    $source,
                    $row->purchase_id
                        ? (int)$row->purchase_id
                        : null
                );
            }
        );
    }

    public function expireDue(
        int $limit = 100
    ): int
    {
        $ids =
            UserPrivilege::find()
                ->select(
                    'id'
                )
                ->where(
                    [
                        'status' =>
                            UserPrivilege::STATUS_ACTIVE,
                    ]
                )
                ->andWhere(
                    [
                        '<=',
                        'active_to',
                        date(
                            'Y-m-d H:i:s'
                        ),
                    ]
                )
                ->orderBy(
                    [
                        'active_to' =>
                            SORT_ASC,
                        'id' =>
                            SORT_ASC,
                    ]
                )
                ->limit(
                    $limit
                )
                ->column();

        $count = 0;

        foreach($ids as $id)
        {
            if(
                $this->expireOne(
                    (int)$id
                )
            )
            {
                $count++;
            }
        }

        return $count;
    }

    private function expireOne(
        int $id
    ): bool
    {
        return (bool)$this->transaction(
            function() use($id): bool
            {
                $row =
                    $this->lockById(
                        $id
                    );

                if(
                    !$row
                    || $row->status
                        !== UserPrivilege::STATUS_ACTIVE
                    || strtotime(
                        (string)$row->active_to
                    ) > time()
                )
                {
                    return false;
                }

                $group =
                    $row->group;

                $oldActiveTo =
                    (string)$row->active_to;

                $row->status =
                    UserPrivilege::STATUS_EXPIRED;

                $row->updated_at =
                    date(
                        'Y-m-d H:i:s'
                    );

                $row->save(
                    false
                );

                if($group)
                {
                    $this->queueRank(
                        $row,
                        'remove',
                        $group->ftbu_rank
                    );
                }

                (new CosmeticService())
                    ->resetForPrivilege(
                        $row
                    );

                $this->history(
                    $row,
                    'expire',
                    $group?->id,
                    null,
                    $oldActiveTo,
                    null,
                    'system',
                    $row->purchase_id
                        ? (int)$row->purchase_id
                        : null
                );

                return true;
            }
        );
    }

    private function grant(
        int $userId,
        int $serverId,
        PrivilegeGroup $newGroup,
        int $days,
        string $source,
        ?int $purchaseId,
        bool $force
    ): UserPrivilege
    {
        if(
            $days < 1
            || $days > 3650
        )
        {
            throw new \DomainException(
                'Некорректный срок привилегии.'
            );
        }

        if(
            !preg_match(
                '/^[a-z0-9_]+$/',
                (string)$newGroup->ftbu_rank
            )
        )
        {
            throw new \DomainException(
                'Некорректный FTBU rank.'
            );
        }

        return $this->transaction(
            function() use(
                $userId,
                $serverId,
                $newGroup,
                $days,
                $source,
                $purchaseId,
                $force
            ): UserPrivilege
            {
                $row =
                    $this->lockByUserServer(
                        $userId,
                        $serverId
                    );

                $now =
                    date(
                        'Y-m-d H:i:s'
                    );

                $oldGroup = null;
                $oldActiveTo = null;
                $wasActive = false;

                if($row)
                {
                    $oldGroup =
                        $row->group;

                    $oldActiveTo =
                        $row->active_to
                            ? (string)$row->active_to
                            : null;

                    $wasActive =
                        $row->status
                            === UserPrivilege::STATUS_ACTIVE
                        && strtotime(
                            (string)$row->active_to
                        ) > time();
                }
                else
                {
                    $row =
                        new UserPrivilege(
                            [
                                'user_id' =>
                                    $userId,
                                'server_id' =>
                                    $serverId,
                                'created_at' =>
                                    $now,
                            ]
                        );
                }

                if(
                    $wasActive
                    && $oldGroup
                    && (int)$oldGroup->id
                        !== (int)$newGroup->id
                    && !$force
                    && (int)$newGroup->level
                        < (int)$oldGroup->level
                )
                {
                    throw new \DomainException(
                        Yii::t(
                            'ui',
                            'Пока действует {status}, нельзя купить более низкий статус.',
                            ['status' => $oldGroup->name]
                        )
                    );
                }

                if(
                    $wasActive
                    && $oldGroup
                    && (int)$oldGroup->id
                        === (int)$newGroup->id
                )
                {
                    $action =
                        'extend';

                    $base =
                        new \DateTimeImmutable(
                            (string)$row->active_to
                        );

                    $activeFrom =
                        (string)$row->active_from;
                }
                elseif(
                    $wasActive
                    && $oldGroup
                    && !$force
                    && (int)$newGroup->level
                        > (int)$oldGroup->level
                )
                {
                    $action =
                        'upgrade';

                    // Покупатель не теряет оставшийся срок:
                    // остаток старого статуса переносится на новый,
                    // затем добавляется купленный период.
                    $base =
                        new \DateTimeImmutable(
                            (string)$row->active_to
                        );

                    $activeFrom =
                        $now;
                }
                elseif(
                    $wasActive
                    && $oldGroup
                    && (int)$oldGroup->id
                        !== (int)$newGroup->id
                )
                {
                    $action =
                        'replace';

                    $base =
                        new \DateTimeImmutable(
                            $now
                        );

                    $activeFrom =
                        $now;
                }
                else
                {
                    $action =
                        'activate';

                    $base =
                        new \DateTimeImmutable(
                            $now
                        );

                    $activeFrom =
                        $now;
                }

                $newActiveTo =
                    $base
                        ->modify(
                            '+' . $days . ' days'
                        )
                        ->format(
                            'Y-m-d H:i:s'
                        );

                $oldGroupId =
                    $oldGroup
                        ? (int)$oldGroup->id
                        : null;

                $row->privilege_group_id =
                    (int)$newGroup->id;

                $row->purchase_id =
                    $purchaseId;

                $row->status =
                    UserPrivilege::STATUS_ACTIVE;

                $row->source =
                    $source;

                $row->active_from =
                    $activeFrom;

                $row->active_to =
                    $newActiveTo;

                $row->updated_at =
                    $now;

                if(!$row->save(false))
                {
                    throw new \RuntimeException(
                        'Не удалось сохранить привилегию.'
                    );
                }

                if(
                    $wasActive
                    && $oldGroup
                    && (int)$oldGroup->id
                        !== (int)$newGroup->id
                )
                {
                    $this->queueRank(
                        $row,
                        'remove',
                        $oldGroup->ftbu_rank
                    );
                }

                // add безопасно выполнять и при продлении:
                // если parent уже назначен, FTBU просто не изменит запись.
                $this->queueRank(
                    $row,
                    'add',
                    $newGroup->ftbu_rank
                );

                (new CosmeticService())
                    ->syncForPrivilege(
                        $row
                    );

                $this->history(
                    $row,
                    $action,
                    $oldGroupId,
                    (int)$newGroup->id,
                    $oldActiveTo,
                    $newActiveTo,
                    $source,
                    $purchaseId
                );

                return $row;
            }
        );
    }

    private function queueRank(
        UserPrivilege $row,
        string $action,
        string $rank
    ): void
    {
        if(
            !in_array(
                $action,
                [
                    'add',
                    'remove',
                ],
                true
            )
        )
        {
            throw new \InvalidArgumentException(
                'Invalid rank action.'
            );
        }

        if(
            !preg_match(
                '/^[a-z0-9_]+$/',
                $rank
            )
        )
        {
            throw new \InvalidArgumentException(
                'Invalid rank.'
            );
        }

        $queue =
            new DeliveryQueue(
                [
                    'purchase_id' =>
                        $row->purchase_id,
                    'user_id' =>
                        $row->user_id,
                    'server_id' =>
                        $row->server_id,
                    'delivery_type' =>
                        'rcon_command',
                    'payload' =>
                        'ranks '
                        . $action
                        . ' {username} '
                        . $rank,
                    'status' =>
                        'pending',
                    'attempts' =>
                        0,
                    'created_at' =>
                        date(
                            'Y-m-d H:i:s'
                        ),
                ]
            );

        if(!$queue->save(false))
        {
            throw new \RuntimeException(
                'Не удалось поставить синхронизацию ранга в очередь.'
            );
        }
    }

    private function history(
        UserPrivilege $row,
        string $action,
        ?int $fromGroupId,
        ?int $toGroupId,
        ?string $oldActiveTo,
        ?string $newActiveTo,
        string $source,
        ?int $purchaseId
    ): void
    {
        $history =
            new PrivilegeHistory(
                [
                    'user_privilege_id' =>
                        $row->id,
                    'user_id' =>
                        $row->user_id,
                    'server_id' =>
                        $row->server_id,
                    'purchase_id' =>
                        $purchaseId,
                    'action' =>
                        $action,
                    'from_group_id' =>
                        $fromGroupId,
                    'to_group_id' =>
                        $toGroupId,
                    'old_active_to' =>
                        $oldActiveTo,
                    'new_active_to' =>
                        $newActiveTo,
                    'source' =>
                        $source,
                    'metadata_json' =>
                        null,
                    'created_at' =>
                        date(
                            'Y-m-d H:i:s'
                        ),
                ]
            );

        if(!$history->save(false))
        {
            throw new \RuntimeException(
                'Не удалось сохранить историю привилегии.'
            );
        }
    }

    private function lockByUserServer(
        int $userId,
        int $serverId
    ): ?UserPrivilege
    {
        $row =
            Yii::$app->db
                ->createCommand(
                    '
                    SELECT id
                    FROM {{%user_privileges}}
                    WHERE user_id = :uid
                      AND server_id = :sid
                    FOR UPDATE
                    ',
                    [
                        ':uid' =>
                            $userId,
                        ':sid' =>
                            $serverId,
                    ]
                )
                ->queryOne();

        return $row
            ? UserPrivilege::findOne(
                (int)$row['id']
            )
            : null;
    }

    private function lockById(
        int $id
    ): ?UserPrivilege
    {
        $row =
            Yii::$app->db
                ->createCommand(
                    '
                    SELECT id
                    FROM {{%user_privileges}}
                    WHERE id = :id
                    FOR UPDATE
                    ',
                    [
                        ':id' => $id,
                    ]
                )
                ->queryOne();

        return $row
            ? UserPrivilege::findOne(
                (int)$row['id']
            )
            : null;
    }

    private function transaction(
        callable $callback
    ): mixed
    {
        $db =
            Yii::$app->db;

        $own =
            $db->getTransaction()
                === null;

        $transaction =
            $own
                ? $db->beginTransaction()
                : null;

        try
        {
            $result =
                $callback();

            if($own)
            {
                $transaction?->commit();
            }

            return $result;
        }
        catch(\Throwable $e)
        {
            if(
                $own
                && $transaction?->isActive
            )
            {
                $transaction->rollBack();
            }

            throw $e;
        }
    }
}
