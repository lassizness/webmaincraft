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
        int $limit = 10000
    ): int
    {
        $count =
            (new TelemetryMaintenanceService())
                ->cleanupRawEvents(
                    $retentionDays,
                    $limit
                );

        $this->stdout(
            'Deleted raw telemetry events: '
            . $count
            . PHP_EOL
        );

        return ExitCode::OK;
    }
}
