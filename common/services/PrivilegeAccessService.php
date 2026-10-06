<?php

declare(strict_types=1);

namespace common\services;

use common\models\UserPrivilege;

final class PrivilegeAccessService
{
    public function activePrivilege(
        int $userId,
        int $serverId
    ): ?UserPrivilege
    {
        return UserPrivilege::find()
            ->with('group')
            ->where([
                'user_id' => $userId,
                'server_id' => $serverId,
                'status' => UserPrivilege::STATUS_ACTIVE,
            ])
            ->andWhere([
                '>',
                'active_to',
                date('Y-m-d H:i:s'),
            ])
            ->one();
    }

    public function hasLevel(
        int $userId,
        int $serverId,
        int $minLevel
    ): bool
    {
        $privilege = $this->activePrivilege(
            $userId,
            $serverId
        );

        return $privilege
            && $privilege->group
            && (int)$privilege->group->level >= $minLevel;
    }

    public function canUseCloak(
        int $userId,
        int $serverId
    ): bool
    {
        // VIP = level 20
        return $this->hasLevel(
            $userId,
            $serverId,
            20
        );
    }

    public function canUseNickColor(
        int $userId,
        int $serverId
    ): bool
    {
        // Premium = level 30
        return $this->hasLevel(
            $userId,
            $serverId,
            30
        );
    }
}
