<?php

declare(strict_types=1);

namespace console\controllers;

use common\models\User;
use common\services\{
    AchievementEvaluatorService,
    AchievementRewardProcessorService
};
use yii\console\Controller;
use yii\console\ExitCode;

final class AchievementController extends Controller
{
    public function actionRecalculate(
        int $batchSize = 200
    ): int
    {
        $batchSize = max(
            10,
            min(1000, $batchSize)
        );

        $lastUserId = 0;
        $processed = 0;
        $unlocked = 0;
        $evaluator =
            new AchievementEvaluatorService();

        while(true)
        {
            $userIds = User::find()
                ->alias('u')
                ->innerJoin(
                    '{{%player_stats}} ps',
                    'ps.user_id = u.id'
                )
                ->select('u.id')
                ->where([
                    'u.status' => User::STATUS_ACTIVE,
                ])
                ->andWhere([
                    '>',
                    'u.id',
                    $lastUserId,
                ])
                ->groupBy('u.id')
                ->orderBy([
                    'u.id' => SORT_ASC,
                ])
                ->limit($batchSize)
                ->column();

            if(!$userIds)
            {
                break;
            }

            foreach($userIds as $userId)
            {
                $userId = (int)$userId;
                $result =
                    $evaluator->evaluateUser($userId);

                $processed++;
                $unlocked += count($result);
                $lastUserId = $userId;
            }

            $this->stdout(
                'Achievement recalculation: processed='
                . $processed
                . ', unlocked='
                . $unlocked
                . PHP_EOL
            );
        }

        $this->stdout(
            'Achievement recalculation complete: processed='
            . $processed
            . ', unlocked='
            . $unlocked
            . PHP_EOL
        );

        return ExitCode::OK;
    }

    public function actionProcessRewards(
        int $limit = 50
    ): int
    {
        $result =
            (new AchievementRewardProcessorService())
                ->process($limit);

        $this->stdout(
            'Achievement rewards: done='
            . $result['done']
            . ', failed='
            . $result['failed']
            . ', skipped='
            . $result['skipped']
            . PHP_EOL
        );

        return ExitCode::OK;
    }
}
