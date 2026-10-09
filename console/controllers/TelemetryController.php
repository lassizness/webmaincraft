<?php

declare(strict_types=1);

namespace console\controllers;

use common\services\TelemetryMaintenanceService;
use yii\console\Controller;
use yii\console\ExitCode;

final class TelemetryController extends Controller
{
    public function actionCloseSessions(
        int $staleSeconds = 300
    ): int
    {
        $count =
            (new TelemetryMaintenanceService())
                ->closeStaleSessions(
                    $staleSeconds
                );

        $this->stdout(
            'Closed stale sessions: '
            . $count
            . PHP_EOL
        );

        return ExitCode::OK;
    }

    public function actionCleanup(
        int $retentionDays = 90,
        int $batchSize = 10000,
        int $maxBatches = 20
    ): int
    {
        $batchSize = max(
            100,
            min(50000, $batchSize)
        );

        $maxBatches = max(
            1,
            min(100, $maxBatches)
        );

        $service =
            new TelemetryMaintenanceService();

        $total = 0;

        for($i = 0; $i < $maxBatches; $i++)
        {
            $count =
                $service->cleanupRawEvents(
                    $retentionDays,
                    $batchSize
                );

            $total += $count;

            if($count < $batchSize)
            {
                break;
            }
        }

        $this->stdout(
            'Deleted raw telemetry events: '
            . $total
            . PHP_EOL
        );

        return ExitCode::OK;
    }
}
