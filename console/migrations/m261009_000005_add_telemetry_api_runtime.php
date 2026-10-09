<?php

declare(strict_types=1);

use yii\db\Migration;

final class m261009_000005_add_telemetry_api_runtime extends Migration
{
    private string $options =
        'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp(): void
    {
        $this->addColumn(
            '{{%telemetry_events}}',
            'session_uuid',
            $this->char(36)->null()->after('username')
        );

        $this->createIndex(
            'idx_telemetry_session',
            '{{%telemetry_events}}',
            ['session_uuid', 'occurred_at', 'id']
        );

        $this->createTable('{{%player_discoveries}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'server_id' => $this->integer()->notNull(),
            'discovery_type' => $this->string(64)->notNull(),
            'object_id' => $this->string(191)->notNull(),
            'metadata_json' => $this->text()->null(),
            'first_seen_at' => $this->dateTime()->notNull(),
        ], $this->options);

        $this->createIndex(
            'uq_player_discovery',
            '{{%player_discoveries}}',
            [
                'user_id',
                'server_id',
                'discovery_type',
                'object_id',
            ],
            true
        );

        $this->createIndex(
            'idx_player_discovery_type',
            '{{%player_discoveries}}',
            ['discovery_type', 'object_id']
        );

        $this->addForeignKey(
            'fk_player_discovery_user',
            '{{%player_discoveries}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_player_discovery_server',
            '{{%player_discoveries}}',
            'server_id',
            '{{%servers}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable('{{%server_telemetry_status}}', [
            'server_id' => $this->integer()->notNull(),
            'telemetry_version' => $this->string(64)->null(),
            'minecraft_version' => $this->string(32)->null(),
            'forge_version' => $this->string(64)->null(),
            'online_players' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'queue_size' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'buffered_events' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'events_received_total' => $this->bigInteger()->unsigned()->notNull()->defaultValue(0),
            'last_batch_size' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'last_batch_at' => $this->dateTime()->null(),
            'last_event_at' => $this->dateTime()->null(),
            'last_heartbeat_at' => $this->dateTime()->null(),
            'last_seen_ip' => $this->string(45)->null(),
            'last_error' => $this->text()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'PRIMARY KEY(server_id)',
        ], $this->options);

        $this->addForeignKey(
            'fk_server_telemetry_status_server',
            '{{%server_telemetry_status}}',
            'server_id',
            '{{%servers}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%server_telemetry_status}}');
        $this->dropTable('{{%player_discoveries}}');

        $this->dropIndex(
            'idx_telemetry_session',
            '{{%telemetry_events}}'
        );

        $this->dropColumn(
            '{{%telemetry_events}}',
            'session_uuid'
        );
    }
}
