<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    Achievement,
    AchievementProgress,
    AchievementUnlock,
    PlayerProfileSetting,
    PlayerStat,
    User
};
use yii\db\Query;

final class AchievementProfileService
{
    public function statsForUser(int $userId): array
    {
        $rows = PlayerStat::find()
            ->select([
                'stat_key',
                'value' => 'SUM(stat_value)',
            ])
            ->where(['user_id' => $userId])
            ->groupBy('stat_key')
            ->orderBy(['value' => SORT_DESC])
            ->asArray()
            ->all();

        $result = [];

        foreach($rows as $row)
        {
            $result[(string)$row['stat_key']] = (int)$row['value'];
        }

        return $result;
    }

    public function unlocksForUser(int $userId): array
    {
        $rows = AchievementUnlock::find()
            ->with('achievement')
            ->where(['user_id' => $userId])
            ->orderBy([
                'unlocked_at' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->all();

        $result = [];

        foreach($rows as $row)
        {
            $result[(int)$row->achievement_id] = $row;
        }

        return $result;
    }

    public function recentUnlocksForUser(
        int $userId,
        int $limit = 8
    ): array
    {
        return AchievementUnlock::find()
            ->with('achievement')
            ->where(['user_id' => $userId])
            ->orderBy([
                'unlocked_at' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->limit(
                max(1, min(50, $limit))
            )
            ->all();
    }

    public function progressForUser(int $userId): array
    {
        $rows = AchievementProgress::find()
            ->where(['user_id' => $userId])
            ->all();

        $result = [];

        foreach($rows as $row)
        {
            $result[(int)$row->achievement_id] = $row;
        }

        return $result;
    }

    public function catalog(): array
    {
        return Achievement::find()
            ->with(['season', 'conditions', 'rewards'])
            ->where(['enabled' => 1])
            ->orderBy([
                'sort' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->all();
    }

    public function summary(int $userId): array
    {
        $row = (new Query())
            ->from('{{%achievement_unlocks}}')
            ->where(['user_id' => $userId])
            ->select([
                'unlocked' => 'COUNT(*)',
                'score' => 'COALESCE(SUM(score_awarded), 0)',
                'last_unlock' => 'MAX(unlocked_at)',
            ])
            ->one();

        $lastUnlock = AchievementUnlock::find()
            ->with('achievement')
            ->where(['user_id' => $userId])
            ->orderBy([
                'unlocked_at' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->one();

        $total = (int)Achievement::find()
            ->where(['enabled' => 1])
            ->count();

        return [
            'unlocked' => (int)($row['unlocked'] ?? 0),
            'score' => (int)($row['score'] ?? 0),
            'last_unlock' => $row['last_unlock'] ?? null,
            'last_unlock_title' =>
                $lastUnlock?->achievement?->title,
            'last_unlock_rarity' =>
                $lastUnlock?->achievement?->rarity,
            'last_unlock_achievement_id' =>
                $lastUnlock?->achievement_id,
            'total' => $total,
        ];
    }

    public function rankForUser(
        int $userId,
        bool $respectPrivacy = false
    ): ?int
    {
        $score = (int)(new Query())
            ->from('{{%achievement_unlocks}}')
            ->where(['user_id' => $userId])
            ->sum('score_awarded');

        if($score <= 0)
        {
            return null;
        }

        $scores = (new Query())
            ->from(['au' => '{{%achievement_unlocks}}'])
            ->innerJoin(
                ['u' => '{{%users}}'],
                'u.id = au.user_id'
            )
            ->leftJoin(
                ['pps' => '{{%player_profile_settings}}'],
                'pps.user_id = u.id'
            )
            ->where(['u.status' => User::STATUS_ACTIVE]);

        if($respectPrivacy)
        {
            $scores->andWhere([
                'or',
                ['pps.public_ranking' => 1],
                ['pps.public_ranking' => null],
            ]);
        }

        $scores
            ->select([
                'user_id' => 'u.id',
                'score' => 'SUM(au.score_awarded)',
            ])
            ->groupBy('u.id');

        $higher = (new Query())
            ->from(['ranking' => $scores])
            ->where(['>', 'score', $score])
            ->count();

        return (int)$higher + 1;
    }

    public function leaderboard(
        int $limit = 100,
        bool $respectPrivacy = true
    ): array
    {
        $query = (new Query())
            ->from(['au' => '{{%achievement_unlocks}}'])
            ->innerJoin(
                ['u' => '{{%users}}'],
                'u.id = au.user_id'
            )
            ->leftJoin(
                ['pps' => '{{%player_profile_settings}}'],
                'pps.user_id = u.id'
            )
            ->where(['u.status' => User::STATUS_ACTIVE]);

        if($respectPrivacy)
        {
            $query->andWhere([
                'or',
                ['pps.public_ranking' => 1],
                ['pps.public_ranking' => null],
            ]);
        }

        $scores = $query
            ->select([
                'user_id' => 'u.id',
                'username' => 'u.username',
                'score' => 'SUM(au.score_awarded)',
                'achievements' => 'COUNT(au.id)',
                'last_unlock' => 'MAX(au.unlocked_at)',
            ])
            ->groupBy(['u.id', 'u.username'])
            ->orderBy([
                'score' => SORT_DESC,
                'achievements' => SORT_DESC,
                'u.username' => SORT_ASC,
            ])
            ->limit($limit)
            ->all();

        $rank = 1;

        foreach($scores as &$row)
        {
            $row['rank'] = $rank++;
            $row['score'] = (int)$row['score'];
            $row['achievements'] = (int)$row['achievements'];
        }
        unset($row);

        return $scores;
    }

    public function profileSettings(int $userId): PlayerProfileSetting
    {
        return PlayerProfileSetting::forUser($userId);
    }
}
