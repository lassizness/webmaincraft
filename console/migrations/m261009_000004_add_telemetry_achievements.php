<?php

declare(strict_types=1);

use yii\db\Migration;

final class m261009_000004_add_telemetry_achievements extends Migration
{
    private string $options =
        'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp(): void
    {
        $this->createTelemetry();
        $this->createAchievements();
        $this->createProfileSettings();
        $this->seedRbac();
        $this->seedSettings();
    }

    private function createTelemetry(): void
    {
        $this->createTable('{{%telemetry_events}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'event_uuid' => $this->char(36)->notNull(),
            'server_id' => $this->integer()->null(),
            'user_id' => $this->bigInteger()->unsigned()->null(),
            'player_uuid' => $this->char(36)->null(),
            'username' => $this->string(16)->null(),
            'event_type' => $this->string(64)->notNull(),
            'source' => $this->string(32)->notNull()->defaultValue('forge'),
            'object_id' => $this->string(191)->null(),
            'amount' => $this->bigInteger()->notNull()->defaultValue(1),
            'metadata_json' => $this->text()->null(),
            'occurred_at' => $this->dateTime()->notNull(),
            'received_at' => $this->dateTime()->notNull(),
        ], $this->options);

        $this->createIndex('uq_telemetry_event_uuid', '{{%telemetry_events}}', 'event_uuid', true);
        $this->createIndex('idx_telemetry_user_time', '{{%telemetry_events}}', ['user_id', 'occurred_at', 'id']);
        $this->createIndex('idx_telemetry_server_time', '{{%telemetry_events}}', ['server_id', 'occurred_at', 'id']);
        $this->createIndex('idx_telemetry_type_time', '{{%telemetry_events}}', ['event_type', 'occurred_at', 'id']);
        $this->createIndex('idx_telemetry_object', '{{%telemetry_events}}', ['object_id', 'event_type']);

        $this->addForeignKey('fk_telemetry_event_server', '{{%telemetry_events}}', 'server_id', '{{%servers}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_telemetry_event_user', '{{%telemetry_events}}', 'user_id', '{{%users}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%player_stats}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'server_id' => $this->integer()->notNull(),
            'stat_key' => $this->string(191)->notNull(),
            'stat_value' => $this->bigInteger()->notNull()->defaultValue(0),
            'updated_at' => $this->dateTime()->notNull(),
        ], $this->options);

        $this->createIndex('uq_player_stat', '{{%player_stats}}', ['user_id', 'server_id', 'stat_key'], true);
        $this->createIndex('idx_player_stat_rank', '{{%player_stats}}', ['stat_key', 'stat_value']);
        $this->addForeignKey('fk_player_stat_user', '{{%player_stats}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_player_stat_server', '{{%player_stats}}', 'server_id', '{{%servers}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%player_sessions}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'session_uuid' => $this->char(36)->notNull(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'server_id' => $this->integer()->notNull(),
            'started_at' => $this->dateTime()->notNull(),
            'ended_at' => $this->dateTime()->null(),
            'duration_seconds' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'last_event_at' => $this->dateTime()->notNull(),
        ], $this->options);

        $this->createIndex('uq_player_session_uuid', '{{%player_sessions}}', 'session_uuid', true);
        $this->createIndex('idx_player_session_user', '{{%player_sessions}}', ['user_id', 'started_at', 'id']);
        $this->createIndex('idx_player_session_online', '{{%player_sessions}}', ['ended_at', 'last_event_at']);
        $this->addForeignKey('fk_player_session_user', '{{%player_sessions}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_player_session_server', '{{%player_sessions}}', 'server_id', '{{%servers}}', 'id', 'CASCADE', 'CASCADE');
    }

    private function createAchievements(): void
    {
        $this->createTable('{{%achievement_seasons}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(64)->notNull(),
            'title' => $this->string(128)->notNull(),
            'description' => $this->text()->null(),
            'status' => $this->string(16)->notNull()->defaultValue('planned'),
            'starts_at' => $this->dateTime()->null(),
            'ends_at' => $this->dateTime()->null(),
            'sort' => $this->integer()->notNull()->defaultValue(100),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $this->options);

        $this->createIndex('uq_achievement_season_code', '{{%achievement_seasons}}', 'code', true);
        $this->createIndex('idx_achievement_season_status', '{{%achievement_seasons}}', ['status', 'starts_at', 'ends_at']);

        $this->createTable('{{%achievements}}', [
            'id' => $this->primaryKey(),
            'season_id' => $this->integer()->null(),
            'code' => $this->string(96)->notNull(),
            'title' => $this->string(191)->notNull(),
            'description' => $this->text()->null(),
            'hint' => $this->text()->null(),
            'category' => $this->string(64)->notNull()->defaultValue('general'),
            'rarity' => $this->string(16)->notNull()->defaultValue('common'),
            'score' => $this->integer()->notNull()->defaultValue(10),
            'icon' => $this->string(255)->null(),
            'hidden' => $this->boolean()->notNull()->defaultValue(0),
            'enabled' => $this->boolean()->notNull()->defaultValue(1),
            'sort' => $this->integer()->notNull()->defaultValue(100),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $this->options);

        $this->createIndex('uq_achievement_code', '{{%achievements}}', 'code', true);
        $this->createIndex('idx_achievement_catalog', '{{%achievements}}', ['enabled', 'category', 'sort', 'id']);
        $this->addForeignKey('fk_achievement_season', '{{%achievements}}', 'season_id', '{{%achievement_seasons}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%achievement_conditions}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'achievement_id' => $this->integer()->notNull(),
            'group_no' => $this->integer()->notNull()->defaultValue(1),
            'stat_key' => $this->string(191)->notNull(),
            'operator' => $this->string(8)->notNull()->defaultValue('>='),
            'target_value' => $this->bigInteger()->notNull(),
            'sort' => $this->integer()->notNull()->defaultValue(100),
        ], $this->options);

        $this->createIndex('idx_achievement_condition', '{{%achievement_conditions}}', ['achievement_id', 'group_no', 'sort', 'id']);
        $this->createIndex('idx_achievement_condition_stat', '{{%achievement_conditions}}', 'stat_key');
        $this->addForeignKey('fk_achievement_condition_achievement', '{{%achievement_conditions}}', 'achievement_id', '{{%achievements}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%achievement_rewards}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'achievement_id' => $this->integer()->notNull(),
            'reward_type' => $this->string(32)->notNull(),
            'payload_json' => $this->text()->null(),
            'sort' => $this->integer()->notNull()->defaultValue(100),
        ], $this->options);

        $this->createIndex('idx_achievement_reward', '{{%achievement_rewards}}', ['achievement_id', 'sort', 'id']);
        $this->addForeignKey('fk_achievement_reward_achievement', '{{%achievement_rewards}}', 'achievement_id', '{{%achievements}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%achievement_progress}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'achievement_id' => $this->integer()->notNull(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'progress_json' => $this->text()->null(),
            'percent' => $this->decimal(5, 2)->notNull()->defaultValue(0),
            'updated_at' => $this->dateTime()->notNull(),
        ], $this->options);

        $this->createIndex('uq_achievement_progress', '{{%achievement_progress}}', ['achievement_id', 'user_id'], true);
        $this->createIndex('idx_achievement_progress_user', '{{%achievement_progress}}', ['user_id', 'updated_at']);
        $this->addForeignKey('fk_achievement_progress_achievement', '{{%achievement_progress}}', 'achievement_id', '{{%achievements}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_achievement_progress_user', '{{%achievement_progress}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%achievement_unlocks}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'achievement_id' => $this->integer()->notNull(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'score_awarded' => $this->integer()->notNull()->defaultValue(0),
            'unlocked_at' => $this->dateTime()->notNull(),
            'reward_status' => $this->string(16)->notNull()->defaultValue('none'),
            'reward_error' => $this->text()->null(),
        ], $this->options);

        $this->createIndex('uq_achievement_unlock', '{{%achievement_unlocks}}', ['achievement_id', 'user_id'], true);
        $this->createIndex('idx_achievement_unlock_user', '{{%achievement_unlocks}}', ['user_id', 'unlocked_at', 'id']);
        $this->createIndex('idx_achievement_unlock_score', '{{%achievement_unlocks}}', ['score_awarded', 'unlocked_at']);
        $this->addForeignKey('fk_achievement_unlock_achievement', '{{%achievement_unlocks}}', 'achievement_id', '{{%achievements}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_achievement_unlock_user', '{{%achievement_unlocks}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
    }

    private function createProfileSettings(): void
    {
        $this->createTable('{{%player_profile_settings}}', [
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'public_achievements' => $this->boolean()->notNull()->defaultValue(1),
            'public_playtime' => $this->boolean()->notNull()->defaultValue(1),
            'public_ranking' => $this->boolean()->notNull()->defaultValue(1),
            'updated_at' => $this->dateTime()->notNull(),
            'PRIMARY KEY(user_id)',
        ], $this->options);

        $this->addForeignKey('fk_player_profile_settings_user', '{{%player_profile_settings}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
    }

    private function seedRbac(): void
    {
        $now = time();

        foreach([
            'viewTelemetry' => 'View game telemetry',
            'manageAchievements' => 'Manage achievements',
            'manageSeasons' => 'Manage achievement seasons',
            'manageRatings' => 'Manage ratings',
        ] as $name => $description)
        {
            $this->execute(
                'INSERT IGNORE INTO {{%auth_item}} '
                . '(name,type,description,created_at,updated_at) '
                . 'VALUES (:name,2,:description,:created,:updated)',
                [
                    ':name' => $name,
                    ':description' => $description,
                    ':created' => $now,
                    ':updated' => $now,
                ]
            );

            $this->execute(
                'INSERT IGNORE INTO {{%auth_item_child}} (parent,child) '
                . 'VALUES (\'admin\', :child)',
                [':child' => $name]
            );
        }
    }

    private function seedSettings(): void
    {
        foreach([
            'ratings.public_enabled' => '0',
            'achievements.enabled' => '1',
        ] as $key => $value)
        {
            $this->insert('{{%settings}}', [
                'key' => $key,
                'value' => $value,
            ]);
        }
    }

    public function safeDown(): void
    {
        $this->delete('{{%auth_item_child}}', [
            'parent' => 'admin',
            'child' => [
                'viewTelemetry',
                'manageAchievements',
                'manageSeasons',
                'manageRatings',
            ],
        ]);

        $this->delete('{{%auth_item}}', [
            'name' => [
                'viewTelemetry',
                'manageAchievements',
                'manageSeasons',
                'manageRatings',
            ],
        ]);

        $this->delete('{{%settings}}', [
            'key' => [
                'ratings.public_enabled',
                'achievements.enabled',
            ],
        ]);

        foreach([
            '{{%player_profile_settings}}',
            '{{%achievement_unlocks}}',
            '{{%achievement_progress}}',
            '{{%achievement_rewards}}',
            '{{%achievement_conditions}}',
            '{{%achievements}}',
            '{{%achievement_seasons}}',
            '{{%player_sessions}}',
            '{{%player_stats}}',
            '{{%telemetry_events}}',
        ] as $table)
        {
            if($this->db->schema->getTableSchema($table, true))
            {
                $this->dropTable($table);
            }
        }
    }
}
