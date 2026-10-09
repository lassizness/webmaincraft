<?php

declare(strict_types=1);

use yii\db\Migration;

final class m261009_000001_add_rbac_moderation extends Migration
{
    private string $options =
        'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp(): void
    {
        $this->createRbacMeta();
        $this->createModeration();
        $this->extendServers();
        $this->seedRbac();
    }

    private function createRbacMeta(): void
    {
        if($this->db->schema->getTableSchema('{{%rbac_role_meta}}', true))
        {
            return;
        }

        $this->createTable('{{%rbac_role_meta}}', [
            'role_name' => $this->string(64)->notNull(),
            'label' => $this->string(128)->notNull(),
            'level' => $this->integer()->notNull(),
            'is_system' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'PRIMARY KEY(role_name)',
        ], $this->options);

        $this->addForeignKey(
            'fk_rbac_role_meta_role',
            '{{%rbac_role_meta}}',
            'role_name',
            '{{%auth_item}}',
            'name',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex(
            'idx_rbac_role_meta_level',
            '{{%rbac_role_meta}}',
            ['level', 'role_name']
        );
    }

    private function createModeration(): void
    {
        if(!$this->db->schema->getTableSchema('{{%moderation_actions}}', true))
        {
            $this->createTable('{{%moderation_actions}}', [
                'id' => $this->bigPrimaryKey()->unsigned(),
                'user_id' => $this->bigInteger()->unsigned()->notNull(),
                'server_id' => $this->integer()->null(),
                'type' => $this->string(16)->notNull(),
                'scope' => $this->string(16)->notNull(),
                'reason' => $this->text()->notNull(),
                'internal_comment' => $this->text()->null(),
                'starts_at' => $this->dateTime()->notNull(),
                'expires_at' => $this->dateTime()->null(),
                'status' => $this->string(16)->notNull(),
                'created_by' => $this->bigInteger()->unsigned()->null(),
                'created_at' => $this->dateTime()->notNull(),
                'revoked_by' => $this->bigInteger()->unsigned()->null(),
                'revoked_at' => $this->dateTime()->null(),
                'revoke_reason' => $this->text()->null(),
                'metadata_json' => $this->text()->null(),
            ], $this->options);

            $this->createIndex(
                'idx_moderation_user_status',
                '{{%moderation_actions}}',
                ['user_id', 'status', 'id']
            );

            $this->createIndex(
                'idx_moderation_expire',
                '{{%moderation_actions}}',
                ['status', 'expires_at', 'id']
            );

            $this->createIndex(
                'idx_moderation_server_status',
                '{{%moderation_actions}}',
                ['server_id', 'status', 'id']
            );

            $this->addForeignKey(
                'fk_moderation_user',
                '{{%moderation_actions}}',
                'user_id',
                '{{%users}}',
                'id',
                'CASCADE',
                'CASCADE'
            );

            $this->addForeignKey(
                'fk_moderation_server',
                '{{%moderation_actions}}',
                'server_id',
                '{{%servers}}',
                'id',
                'SET NULL',
                'CASCADE'
            );

            $this->addForeignKey(
                'fk_moderation_created_by',
                '{{%moderation_actions}}',
                'created_by',
                '{{%users}}',
                'id',
                'SET NULL',
                'CASCADE'
            );

            $this->addForeignKey(
                'fk_moderation_revoked_by',
                '{{%moderation_actions}}',
                'revoked_by',
                '{{%users}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
        }

        if(!$this->db->schema->getTableSchema('{{%moderation_action_history}}', true))
        {
            $this->createTable('{{%moderation_action_history}}', [
                'id' => $this->bigPrimaryKey()->unsigned(),
                'action_id' => $this->bigInteger()->unsigned()->notNull(),
                'event_type' => $this->string(32)->notNull(),
                'actor_id' => $this->bigInteger()->unsigned()->null(),
                'data_json' => $this->text()->null(),
                'created_at' => $this->dateTime()->notNull(),
            ], $this->options);

            $this->createIndex(
                'idx_moderation_history_action',
                '{{%moderation_action_history}}',
                ['action_id', 'id']
            );

            $this->addForeignKey(
                'fk_moderation_history_action',
                '{{%moderation_action_history}}',
                'action_id',
                '{{%moderation_actions}}',
                'id',
                'CASCADE',
                'CASCADE'
            );

            $this->addForeignKey(
                'fk_moderation_history_actor',
                '{{%moderation_action_history}}',
                'actor_id',
                '{{%users}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
        }
    }

    private function extendServers(): void
    {
        $schema = $this->db->schema->getTableSchema('{{%servers}}', true);

        foreach([
            'moderation_ban_command' => $this->string(512)->null(),
            'moderation_unban_command' => $this->string(512)->null(),
            'moderation_mute_command' => $this->string(512)->null(),
            'moderation_unmute_command' => $this->string(512)->null(),
            'moderation_warn_command' => $this->string(512)->null(),
        ] as $column => $definition)
        {
            if(!$schema?->getColumn($column))
            {
                $this->addColumn('{{%servers}}', $column, $definition);
            }
        }

        $this->update('{{%servers}}', [
            'moderation_ban_command' => 'ban {username} {reason}',
            'moderation_unban_command' => 'pardon {username}',
        ], [
            'and',
            ['moderation_ban_command' => null],
            ['moderation_unban_command' => null],
        ]);
    }

    private function seedRbac(): void
    {
        $now = time();

        $permissions = [
            'viewUsers' => 'View users',
            'manageUserStatus' => 'Manage user account status',
            'manageUserRoles' => 'Assign staff roles',
            'manageLauncherPermissions' => 'Manage launcher permissions',
            'viewModeration' => 'View moderation actions',
            'createWarn' => 'Create warnings',
            'createMute' => 'Create server mutes',
            'createGlobalMute' => 'Create global mutes',
            'createBan' => 'Create server bans',
            'createGlobalBan' => 'Create global bans',
            'revokePunishment' => 'Revoke moderation actions',
            'manageRbac' => 'Manage staff roles and permissions',
        ];

        foreach($permissions as $name => $description)
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
        }

        foreach([
            'viewUsers',
            'manageUserStatus',
            'manageUserRoles',
            'manageLauncherPermissions',
        ] as $child)
        {
            $this->execute(
                'INSERT IGNORE INTO {{%auth_item_child}} (parent,child) '
                . 'VALUES (\'manageUsers\', :child)',
                [':child' => $child]
            );
        }

        foreach([
            'support' => 'Support',
            'content_manager' => 'Content manager',
            'senior_moderator' => 'Senior moderator',
        ] as $name => $description)
        {
            $this->execute(
                'INSERT IGNORE INTO {{%auth_item}} '
                . '(name,type,description,created_at,updated_at) '
                . 'VALUES (:name,1,:description,:created,:updated)',
                [
                    ':name' => $name,
                    ':description' => $description,
                    ':created' => $now,
                    ':updated' => $now,
                ]
            );
        }

        $roleChildren = [
            'moderator' => [
                'viewUsers',
                'viewModeration',
                'createWarn',
                'createMute',
                'createBan',
            ],
            'senior_moderator' => [
                'user',
                'adminPanel',
                'viewUsers',
                'viewModeration',
                'createWarn',
                'createMute',
                'createGlobalMute',
                'createBan',
                'createGlobalBan',
                'revokePunishment',
                'manageSupport',
            ],
            'support' => [
                'user',
                'adminPanel',
                'viewUsers',
                'manageSupport',
            ],
            'content_manager' => [
                'user',
                'adminPanel',
                'manageContent',
            ],
            'admin' => [
                'viewModeration',
                'createGlobalMute',
                'createGlobalBan',
                'revokePunishment',
                'manageRbac',
            ],
        ];

        foreach($roleChildren as $parent => $children)
        {
            foreach($children as $child)
            {
                $this->execute(
                    'INSERT IGNORE INTO {{%auth_item_child}} (parent,child) '
                    . 'VALUES (:parent,:child)',
                    [
                        ':parent' => $parent,
                        ':child' => $child,
                    ]
                );
            }
        }

        foreach([
            ['admin', 'Администратор', 100, 1],
            ['senior_moderator', 'Старший модератор', 30, 1],
            ['moderator', 'Модератор', 20, 1],
            ['content_manager', 'Контент-менеджер', 15, 1],
            ['support', 'Поддержка', 10, 1],
        ] as [$role, $label, $level, $system])
        {
            $this->execute(
                'INSERT IGNORE INTO {{%rbac_role_meta}} '
                . '(role_name,label,level,is_system,created_at,updated_at) '
                . 'VALUES (:role,:label,:level,:system,NOW(),NOW())',
                [
                    ':role' => $role,
                    ':label' => $label,
                    ':level' => $level,
                    ':system' => $system,
                ]
            );
        }
    }

    public function safeDown(): void
    {
        foreach([
            'moderation_warn_command',
            'moderation_unmute_command',
            'moderation_mute_command',
            'moderation_unban_command',
            'moderation_ban_command',
        ] as $column)
        {
            $schema = $this->db->schema->getTableSchema('{{%servers}}', true);

            if($schema?->getColumn($column))
            {
                $this->dropColumn('{{%servers}}', $column);
            }
        }

        if($this->db->schema->getTableSchema('{{%moderation_action_history}}', true))
        {
            $this->dropTable('{{%moderation_action_history}}');
        }

        if($this->db->schema->getTableSchema('{{%moderation_actions}}', true))
        {
            $this->dropTable('{{%moderation_actions}}');
        }

        if($this->db->schema->getTableSchema('{{%rbac_role_meta}}', true))
        {
            $this->dropTable('{{%rbac_role_meta}}');
        }

        $this->delete('{{%auth_item}}', [
            'name' => [
                'viewUsers',
                'manageUserStatus',
                'manageUserRoles',
                'manageLauncherPermissions',
                'viewModeration',
                'createWarn',
                'createMute',
                'createGlobalMute',
                'createBan',
                'createGlobalBan',
                'revokePunishment',
                'manageRbac',
                'support',
                'content_manager',
                'senior_moderator',
            ],
        ]);
    }
}
