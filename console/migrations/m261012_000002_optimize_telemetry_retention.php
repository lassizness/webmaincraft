<?php

declare(strict_types=1);

use yii\db\Migration;

final class m261012_000002_optimize_telemetry_retention extends Migration
{
    public function safeUp(): void
    {
        $this->createIndex(
            'idx_telemetry_received_id',
            '{{%telemetry_events}}',
            ['received_at', 'id']
        );
    }

    public function safeDown(): void
    {
        $this->dropIndex(
            'idx_telemetry_received_id',
            '{{%telemetry_events}}'
        );
    }
}
