<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class Setting extends ActiveRecord
{
    public static function tableName(): string { return '{{%settings}}'; }
    public static function value(string $key, mixed $default=null): mixed { $m=self::findOne(['key'=>$key]); return $m?->value ?? $default; }
}
