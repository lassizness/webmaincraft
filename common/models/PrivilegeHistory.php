<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class PrivilegeHistory extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%privilege_history}}';
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

    public function getFromGroup()
    {
        return $this->hasOne(
            PrivilegeGroup::class,
            [
                'id' => 'from_group_id',
            ]
        );
    }

    public function getToGroup()
    {
        return $this->hasOne(
            PrivilegeGroup::class,
            [
                'id' => 'to_group_id',
            ]
        );
    }
}
