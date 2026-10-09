<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class AchievementReward extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%achievement_rewards}}';
    }
}
