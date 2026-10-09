<?php

declare(strict_types=1);

namespace console\controllers;

use common\services\AchievementRewardProcessorService;
use yii\console\Controller;
use yii\console\ExitCode;

final class AchievementController extends Controller
{
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
