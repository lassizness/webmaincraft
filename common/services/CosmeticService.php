<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    DeliveryQueue,
    PrivilegeGroup,
    UserCosmetic,
    UserPrivilege
};

final class CosmeticService
{
    public const COLORS = [
        'dark_blue' => [
            'label' => 'Тёмно-синий',
            'code' => '&1',
            'hex' => '#0000AA',
        ],
        'dark_green' => [
            'label' => 'Тёмно-зелёный',
            'code' => '&2',
            'hex' => '#00AA00',
        ],
        'dark_aqua' => [
            'label' => 'Бирюзовый',
            'code' => '&3',
            'hex' => '#00AAAA',
        ],
        'dark_red' => [
            'label' => 'Тёмно-красный',
            'code' => '&4',
            'hex' => '#AA0000',
        ],
        'dark_purple' => [
            'label' => 'Фиолетовый',
            'code' => '&5',
            'hex' => '#AA00AA',
        ],
        'gold' => [
            'label' => 'Золотой',
            'code' => '&6',
            'hex' => '#FFAA00',
        ],
        'gray' => [
            'label' => 'Серый',
            'code' => '&7',
            'hex' => '#AAAAAA',
        ],
        'blue' => [
            'label' => 'Синий',
            'code' => '&9',
            'hex' => '#5555FF',
        ],
        'green' => [
            'label' => 'Зелёный',
            'code' => '&a',
            'hex' => '#55FF55',
        ],
        'aqua' => [
            'label' => 'Голубой',
            'code' => '&b',
            'hex' => '#55FFFF',
        ],
        'red' => [
            'label' => 'Красный',
            'code' => '&c',
            'hex' => '#FF5555',
        ],
        'light_purple' => [
            'label' => 'Розово-фиолетовый',
            'code' => '&d',
            'hex' => '#FF55FF',
        ],
        'yellow' => [
            'label' => 'Жёлтый',
            'code' => '&e',
            'hex' => '#FFFF55',
        ],
        'white' => [
            'label' => 'Белый',
            'code' => '&f',
            'hex' => '#FFFFFF',
        ],
    ];

    public function setNickColor(
        int $userId,
        int $serverId,
        ?string $color
    ): void
    {
        $access = new PrivilegeAccessService();

        $privilege = $access->activePrivilege(
            $userId,
            $serverId
        );

        if(
            !$privilege
            || !$privilege->group
            || (int)$privilege->group->level < 30
        )
        {
            throw new \DomainException(
                'Выбор цвета ника доступен начиная со статуса Premium.'
            );
        }

        if(
            $color !== null
            && !isset(self::COLORS[$color])
        )
        {
            throw new \DomainException(
                'Недопустимый цвет.'
            );
        }

        $model = UserCosmetic::findOne([
            'user_id' => $userId,
            'server_id' => $serverId,
        ]);

        if(!$model)
        {
            $model = new UserCosmetic([
                'user_id' => $userId,
                'server_id' => $serverId,
            ]);
        }

        $model->nick_color = $color;

        if(!$model->save())
        {
            throw new \RuntimeException(
                'Не удалось сохранить цвет ника.'
            );
        }

        if($color === null)
        {
            $this->queueReset(
                $userId,
                $serverId
            );

            return;
        }

        $this->queueFormat(
            $userId,
            $serverId,
            $privilege->group,
            $color
        );
    }

    public function syncForPrivilege(
        UserPrivilege $privilege
    ): void
    {
        $group = $privilege->group;

        if(
            !$group
            || $privilege->status
                !== UserPrivilege::STATUS_ACTIVE
            || strtotime((string)$privilege->active_to)
                <= time()
        )
        {
            $this->queueReset(
                (int)$privilege->user_id,
                (int)$privilege->server_id
            );

            return;
        }

        $cosmetic = UserCosmetic::findOne([
            'user_id' => $privilege->user_id,
            'server_id' => $privilege->server_id,
        ]);

        if(
            (int)$group->level < 30
            || !$cosmetic
            || !$cosmetic->nick_color
            || !isset(
                self::COLORS[
                    $cosmetic->nick_color
                ]
            )
        )
        {
            $this->queueReset(
                (int)$privilege->user_id,
                (int)$privilege->server_id
            );

            return;
        }

        $this->queueFormat(
            (int)$privilege->user_id,
            (int)$privilege->server_id,
            $group,
            (string)$cosmetic->nick_color
        );
    }

    public function resetForPrivilege(
        UserPrivilege $privilege
    ): void
    {
        $this->queueReset(
            (int)$privilege->user_id,
            (int)$privilege->server_id
        );
    }

    private function queueFormat(
        int $userId,
        int $serverId,
        PrivilegeGroup $group,
        string $color
    ): void
    {
        $colorCode =
            self::COLORS[$color]['code'];

        $prefix =
            $this->prefixForGroup(
                (string)$group->code
            );

        $format =
            '<'
            . $prefix
            . ' '
            . $colorCode
            . '{name}&r>';

        $this->queue(
            $userId,
            $serverId,
            'ranks set_permission {username} '
            . 'ftbutilities.chat.name_format '
            . '"'
            . $format
            . '"'
        );
    }

    private function queueReset(
        int $userId,
        int $serverId
    ): void
    {
        $this->queue(
            $userId,
            $serverId,
            'ranks set_permission {username} '
            . 'ftbutilities.chat.name_format none'
        );
    }

    private function queue(
        int $userId,
        int $serverId,
        string $command
    ): void
    {
        $queue = new DeliveryQueue([
            'purchase_id' => null,
            'user_id' => $userId,
            'server_id' => $serverId,
            'delivery_type' => 'cosmetic',
            'payload' => $command,
            'status' => 'pending',
            'attempts' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if(!$queue->save(false))
        {
            throw new \RuntimeException(
                'Не удалось поставить изменение ника в очередь.'
            );
        }
    }

    private function prefixForGroup(
        string $code
    ): string
    {
        return match($code)
        {
            'vip' =>
                '&a[VIP]&r',

            'premium' =>
                '&b[PREMIUM]&r',

            'elite' =>
                '&6[ELITE]&r',

            'legend' =>
                '&d[LEGEND]&r',

            default =>
                '',
        };
    }
}
