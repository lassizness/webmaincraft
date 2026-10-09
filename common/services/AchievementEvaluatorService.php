<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    Achievement,
    AchievementSeason
};
use Yii;
use yii\db\Query;

final class AchievementEvaluatorService
{
    public function evaluateUser(
        int $userId
    ): array
    {
        $stats = $this->stats($userId);
        $now = time();
        $unlocked = [];

        $achievements = Achievement::find()
            ->with(['season', 'conditions', 'rewards'])
            ->where(['enabled' => 1])
            ->orderBy([
                'sort' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->all();

        foreach($achievements as $achievement)
        {
            if(!$this->seasonAllows($achievement, $now))
            {
                continue;
            }

            $conditions = $achievement->conditions;

            if(!$conditions)
            {
                continue;
            }

            [$satisfied, $percent, $progress] =
                $this->evaluateConditions(
                    $conditions,
                    $stats
                );

            $this->saveProgress(
                (int)$achievement->id,
                $userId,
                $percent,
                $progress
            );

            if(!$satisfied)
            {
                continue;
            }

            $inserted = Yii::$app->db
                ->createCommand(
                    '
                    INSERT IGNORE INTO {{%achievement_unlocks}}
                    (
                        achievement_id,
                        user_id,
                        score_awarded,
                        unlocked_at,
                        reward_status,
                        reward_error
                    )
                    VALUES
                    (
                        :achievement_id,
                        :user_id,
                        :score_awarded,
                        :unlocked_at,
                        :reward_status,
                        NULL
                    )
                    ',
                    [
                        ':achievement_id' =>
                            (int)$achievement->id,
                        ':user_id' =>
                            $userId,
                        ':score_awarded' =>
                            max(0, (int)$achievement->score),
                        ':unlocked_at' =>
                            date('Y-m-d H:i:s'),
                        ':reward_status' =>
                            $achievement->rewards
                                ? 'pending'
                                : 'none',
                    ]
                )
                ->execute();

            if($inserted > 0)
            {
                $unlocked[] = [
                    'id' => (int)$achievement->id,
                    'code' => (string)$achievement->code,
                    'title' => (string)$achievement->title,
                    'score' => max(
                        0,
                        (int)$achievement->score
                    ),
                ];
            }
        }

        return $unlocked;
    }

    private function stats(
        int $userId
    ): array
    {
        $rows = (new Query())
            ->from('{{%player_stats}}')
            ->where(['user_id' => $userId])
            ->select([
                'stat_key',
                'value' => 'SUM(stat_value)',
            ])
            ->groupBy('stat_key')
            ->all();

        $stats = [];

        foreach($rows as $row)
        {
            $stats[(string)$row['stat_key']] =
                (int)$row['value'];
        }

        return $stats;
    }

    private function seasonAllows(
        Achievement $achievement,
        int $now
    ): bool
    {
        if(!$achievement->season_id)
        {
            return true;
        }

        $season = $achievement->season;

        if(
            !$season
            || $season->status
                !== AchievementSeason::STATUS_ACTIVE
        )
        {
            return false;
        }

        if(
            $season->starts_at
            && strtotime((string)$season->starts_at) > $now
        )
        {
            return false;
        }

        if(
            $season->ends_at
            && strtotime((string)$season->ends_at) < $now
        )
        {
            return false;
        }

        return true;
    }

    private function evaluateConditions(
        array $conditions,
        array $stats
    ): array
    {
        $groups = [];

        foreach($conditions as $condition)
        {
            $groups[
                max(1, (int)$condition->group_no)
            ][] = $condition;
        }

        $bestPercent = 0.0;
        $anyGroupSatisfied = false;
        $details = [];

        foreach($groups as $groupNo => $items)
        {
            $groupSatisfied = true;
            $groupPercent = 100.0;
            $groupDetails = [];

            foreach($items as $condition)
            {
                $key = (string)$condition->stat_key;
                $current = (int)($stats[$key] ?? 0);
                $target = (int)$condition->target_value;
                $operator = (string)$condition->operator;

                $matched = $this->matches(
                    $current,
                    $operator,
                    $target
                );

                $conditionPercent =
                    $this->conditionPercent(
                        $current,
                        $operator,
                        $target,
                        $matched
                    );

                $groupSatisfied =
                    $groupSatisfied && $matched;

                $groupPercent = min(
                    $groupPercent,
                    $conditionPercent
                );

                $groupDetails[] = [
                    'stat_key' => $key,
                    'operator' => $operator,
                    'target' => $target,
                    'current' => $current,
                    'matched' => $matched,
                    'percent' => round(
                        $conditionPercent,
                        2
                    ),
                ];
            }

            if($groupSatisfied)
            {
                $anyGroupSatisfied = true;
                $groupPercent = 100.0;
            }

            $bestPercent = max(
                $bestPercent,
                $groupPercent
            );

            $details[] = [
                'group' => (int)$groupNo,
                'matched' => $groupSatisfied,
                'percent' => round(
                    $groupPercent,
                    2
                ),
                'conditions' => $groupDetails,
            ];
        }

        return [
            $anyGroupSatisfied,
            $anyGroupSatisfied
                ? 100.0
                : min(99.99, $bestPercent),
            [
                'logic' => 'OR_OF_AND_GROUPS',
                'groups' => $details,
            ],
        ];
    }

    private function matches(
        int $current,
        string $operator,
        int $target
    ): bool
    {
        return match($operator) {
            '>=' => $current >= $target,
            '<=' => $current <= $target,
            '=' => $current === $target,
            '>' => $current > $target,
            '<' => $current < $target,
            default => false,
        };
    }

    private function conditionPercent(
        int $current,
        string $operator,
        int $target,
        bool $matched
    ): float
    {
        if($matched)
        {
            return 100.0;
        }

        if(
            in_array(
                $operator,
                ['>=', '>'],
                true
            )
            && $target > 0
        )
        {
            return max(
                0.0,
                min(
                    99.99,
                    ($current / $target) * 100
                )
            );
        }

        return 0.0;
    }

    private function saveProgress(
        int $achievementId,
        int $userId,
        float $percent,
        array $progress
    ): void
    {
        $json = json_encode(
            $progress,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        Yii::$app->db
            ->createCommand(
                '
                INSERT INTO {{%achievement_progress}}
                (
                    achievement_id,
                    user_id,
                    progress_json,
                    percent,
                    updated_at
                )
                VALUES
                (
                    :achievement_id,
                    :user_id,
                    :progress_json,
                    :percent,
                    :updated_at
                )
                ON DUPLICATE KEY UPDATE
                    progress_json = VALUES(progress_json),
                    percent = VALUES(percent),
                    updated_at = VALUES(updated_at)
                ',
                [
                    ':achievement_id' => $achievementId,
                    ':user_id' => $userId,
                    ':progress_json' => $json,
                    ':percent' => round($percent, 2),
                    ':updated_at' => date('Y-m-d H:i:s'),
                ]
            )
            ->execute();
    }
}
