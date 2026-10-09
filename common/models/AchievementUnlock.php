<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class AchievementUnlock extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%achievement_unlocks}}';
    }

    public function getAchievement()
    {
        return $this->hasOne(
            Achievement::class,
            ['id' => 'achievement_id']
        );
    }

    public function getUser()
    {
        return $this->hasOne(
            User::class,
            ['id' => 'user_id']
        );
    }
}
