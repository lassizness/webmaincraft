<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class AchievementProgress extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%achievement_progress}}';
    }

    public function getAchievement()
    {
        return $this->hasOne(
            Achievement::class,
            ['id' => 'achievement_id']
        );
    }
}
