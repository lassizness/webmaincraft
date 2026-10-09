<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class AchievementCondition extends ActiveRecord
{
    public const OPERATORS = ['>=', '<=', '=', '>', '<'];

    public static function tableName(): string
    {
        return '{{%achievement_conditions}}';
    }

    public function rules(): array
    {
        return [
            [['achievement_id', 'stat_key', 'target_value'], 'required'],
            [['achievement_id', 'group_no', 'target_value', 'sort'], 'integer'],
            [['stat_key'], 'string', 'max' => 191],
            [['operator'], 'string', 'max' => 8],
            ['operator', 'in', 'range' => self::OPERATORS],
        ];
    }
}
