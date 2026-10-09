<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class ModerationActionHistory extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%moderation_action_history}}';
    }

    public function getAction()
    {
        return $this->hasOne(ModerationAction::class, ['id' => 'action_id']);
    }

    public function getActor()
    {
        return $this->hasOne(User::class, ['id' => 'actor_id']);
    }
}
