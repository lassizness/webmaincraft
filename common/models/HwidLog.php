<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class HwidLog extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%hwidLog}}';
    }

    public function getHwid()
    {
        return $this->hasOne(Hwid::class, ['id' => 'hwidId']);
    }
}
