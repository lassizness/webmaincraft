<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class AchievementRewardDelivery extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_DONE = 'done';
    public const STATUS_FAILED = 'failed';

    public static function tableName(): string
    {
        return '{{%achievement_reward_deliveries}}';
    }

    public function getUnlock()
    {
        return $this->hasOne(
            AchievementUnlock::class,
            ['id' => 'unlock_id']
        );
    }

    public function getReward()
    {
        return $this->hasOne(
            AchievementReward::class,
            ['id' => 'reward_id']
        );
    }
}
