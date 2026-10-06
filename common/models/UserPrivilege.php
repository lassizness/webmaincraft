<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class UserPrivilege extends ActiveRecord
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVOKED = 'revoked';

    public static function tableName(): string
    {
        return '{{%user_privileges}}';
    }

    public function getUser()
    {
        return $this->hasOne(
            User::class,
            [
                'id' => 'user_id',
            ]
        );
    }

    public function getServer()
    {
        return $this->hasOne(
            Server::class,
            [
                'id' => 'server_id',
            ]
        );
    }

    public function getGroup()
    {
        return $this->hasOne(
            PrivilegeGroup::class,
            [
                'id' => 'privilege_group_id',
            ]
        );
    }

    public function getPurchase()
    {
        return $this->hasOne(
            ShopPurchase::class,
            [
                'id' => 'purchase_id',
            ]
        );
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && strtotime(
                (string)$this->active_to
            ) > time();
    }
}
