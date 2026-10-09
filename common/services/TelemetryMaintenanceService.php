<?php

declare(strict_types=1);

namespace common\services;

use Yii;

final class TelemetryMaintenanceService
{
    public function closeStaleSessions(
        int $staleSeconds = 300
    ): int
    {
        $staleSeconds = max(
            120,
            min(86400, $staleSeconds)
        );

        $cutoff = date(
            'Y-m-d H:i:s',
            time() - $staleSeconds
        );

        return Yii::$app->db
            ->createCommand(
                '
                UPDATE {{%player_sessions}}
                SET
                    ended_at = last_event_at,
                    duration_seconds =
                        GREATEST(
                            0,
                            TIMESTAMPDIFF(
                                SECOND,
                                started_at,
                                last_event_at
                            )
                        )
                WHERE ended_at IS NULL
                  AND last_event_at < :cutoff
                ',
                [':cutoff' => $cutoff]
            )
            ->execute();
    }

    public function cleanupRawEvents(
        int $retentionDays = 90,
        int $limit = 10000
    ): int
    {
        $retentionDays = max(
            7,
            min(3650, $retentionDays)
        );

        $limit = max(
            100,
            min(50000, $limit)
        );

        $cutoff = date(
            'Y-m-d H:i:s',
            strtotime(
                '-' . $retentionDays . ' days'
            )
        );

        return Yii::$app->db
            ->createCommand(
                '
                DELETE FROM {{%telemetry_events}}
                WHERE received_at < :cutoff
                ORDER BY id ASC
                LIMIT ' . $limit,
                [':cutoff' => $cutoff]
            )
            ->execute();
    }
}
