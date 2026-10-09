<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class PlayerProfileSetting extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%player_profile_settings}}';
    }

    public static function forUser(int $userId): self
    {
        $model = self::findOne($userId);

        if($model)
        {
            return $model;
        }

        return new self([
            'user_id' => $userId,
            'public_achievements' => 1,
            'public_playtime' => 1,
            'public_ranking' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function rules(): array
    {
        return [
            [[
                'public_achievements',
                'public_playtime',
                'public_ranking',
            ], 'boolean'],
        ];
    }
}
