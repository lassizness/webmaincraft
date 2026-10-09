<?php

declare(strict_types=1);

use yii\db\Migration;

final class m261009_000002_add_moderation_bridge extends Migration
{
    private string $options =
        'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp(): void
    {
        if(
            $this->db->schema->getTableSchema(
                '{{%server_moderation_bridge}}',
                true
            )
        )
        {
            return;
        }

        $this->createTable(
            '{{%server_moderation_bridge}}',
            [
                'server_id' => $this->integer()->notNull(),
                'token_hash' =>
                    'char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
                'token_hint' => $this->string(24)->null(),
                'enabled' => $this->boolean()->notNull()->defaultValue(1),

                'bridge_version' => $this->string(64)->null(),
                'minecraft_version' => $this->string(32)->null(),
                'forge_version' => $this->string(64)->null(),

                'last_revision' =>
                    $this->bigInteger()->unsigned()->notNull()->defaultValue(0),
                'cached_actions' =>
                    $this->integer()->notNull()->defaultValue(0),
                'online_players' =>
                    $this->integer()->notNull()->defaultValue(0),

                'last_sync_at' => $this->dateTime()->null(),
                'last_heartbeat_at' => $this->dateTime()->null(),
                'last_used_at' => $this->dateTime()->null(),
                'last_seen_ip' => $this->string(45)->null(),
                'last_error' => $this->text()->null(),

                'created_at' => $this->dateTime()->notNull(),
                'rotated_at' => $this->dateTime()->notNull(),
                'updated_at' => $this->dateTime()->notNull(),
            ],
            $this->options
        );

        $this->addPrimaryKey(
            'pk_server_moderation_bridge',
            '{{%server_moderation_bridge}}',
            'server_id'
        );

        $this->createIndex(
            'idx_bridge_enabled_heartbeat',
            '{{%server_moderation_bridge}}',
            [
                'enabled',
                'last_heartbeat_at',
            ]
        );

        $this->addForeignKey(
            'fk_bridge_server',
            '{{%server_moderation_bridge}}',
            'server_id',
            '{{%servers}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        if(
            $this->db->schema->getTableSchema(
                '{{%server_moderation_bridge}}',
                true
            )
        )
        {
            $this->dropTable(
                '{{%server_moderation_bridge}}'
            );
        }
    }
}
