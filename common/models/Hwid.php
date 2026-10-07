<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class Hwid extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%hwids}}';
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, ['hwidId' => 'id']);
    }

    public function getLogs()
    {
        return $this->hasMany(HwidLog::class, ['hwidId' => 'id'])
            ->orderBy(['id' => SORT_DESC]);
    }
}
