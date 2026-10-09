<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class AchievementSeason extends ActiveRecord
{
    public const STATUS_PLANNED = 'planned';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    public static function tableName(): string
    {
        return '{{%achievement_seasons}}';
    }

    public function rules(): array
    {
        return [
            [['code', 'title'], 'required'],
            [['description'], 'string'],
            [['sort'], 'integer'],
            [['starts_at', 'ends_at'], 'safe'],
            [['code'], 'string', 'max' => 64],
            [['title'], 'string', 'max' => 128],
            ['code', 'match', 'pattern' => '/^[a-z0-9_-]+$/'],
            ['code', 'unique'],
            ['status', 'in', 'range' => [
                self::STATUS_PLANNED,
                self::STATUS_ACTIVE,
                self::STATUS_CLOSED,
            ]],
        ];
    }
}
