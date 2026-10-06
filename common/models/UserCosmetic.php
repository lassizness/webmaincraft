<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class UserCosmetic extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%user_cosmetics}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'server_id'], 'required'],
            [['user_id', 'server_id'], 'integer'],
            [['nick_color'], 'string', 'max' => 32],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(
            User::class,
            ['id' => 'user_id']
        );
    }

    public function getServer()
    {
        return $this->hasOne(
            Server::class,
            ['id' => 'server_id']
        );
    }
}
