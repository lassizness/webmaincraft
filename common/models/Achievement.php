<?php

declare(strict_types=1);

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

final class Achievement extends ActiveRecord
{
    public const RARITIES = [
        'common',
        'uncommon',
        'rare',
        'epic',
        'legendary',
    ];

    public static function tableName(): string
    {
        return '{{%achievements}}';
    }

    public function rules(): array
    {
        return [
            [['code', 'title', 'category', 'rarity'], 'required'],
            [['description', 'hint'], 'string'],
            [['season_id', 'score', 'sort'], 'integer'],
            [['hidden', 'enabled'], 'boolean'],
            [['code'], 'string', 'max' => 96],
            [['title'], 'string', 'max' => 191],
            [['category'], 'string', 'max' => 64],
            [['rarity'], 'string', 'max' => 16],
            [['icon'], 'string', 'max' => 255],
            ['code', 'match', 'pattern' => '/^[a-z0-9_.-]+$/'],
            ['code', 'unique'],
            ['rarity', 'in', 'range' => self::RARITIES],
        ];
    }

    public function getSeason()
    {
        return $this->hasOne(
            AchievementSeason::class,
            ['id' => 'season_id']
        );
    }

    public function getConditions()
    {
        return $this->hasMany(
            AchievementCondition::class,
            ['achievement_id' => 'id']
        )->orderBy([
            'group_no' => SORT_ASC,
            'sort' => SORT_ASC,
            'id' => SORT_ASC,
        ]);
    }

    public function getRewards()
    {
        return $this->hasMany(
            AchievementReward::class,
            ['achievement_id' => 'id']
        )->orderBy([
            'sort' => SORT_ASC,
            'id' => SORT_ASC,
        ]);
    }

    public function getDisplayTitle(): string
    {
        return $this->localizedField(
            'title',
            (string)$this->title
        );
    }

    public function getDisplayDescription(): string
    {
        return $this->localizedField(
            'description',
            (string)$this->description
        );
    }

    public function getDisplayHint(): string
    {
        return $this->localizedField(
            'hint',
            (string)$this->hint
        );
    }

    private function localizedField(
        string $field,
        string $fallback
    ): string {
        if(
            Yii::$app->language === 'ru'
            || trim((string)$this->code) === ''
        )
        {
            return $fallback;
        }

        $key = $field . '.' . $this->code;
        $translated = Yii::t(
            'achievements',
            $key
        );

        return $translated !== $key
            ? $translated
            : $fallback;
    }
}
