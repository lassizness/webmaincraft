<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class PlayerStat extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%player_stats}}';
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getServer()
    {
        return $this->hasOne(Server::class, ['id' => 'server_id']);
    }
}
