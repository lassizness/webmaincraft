<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260810_000002_add_privileges extends Migration
{
    private string $options =
        'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp(): void
    {
        $this->createTable(
            '{{%privilege_groups}}',
            [
                'id' => $this->primaryKey(),
                'code' => $this->string(64)->notNull(),
                'name' => $this->string(128)->notNull(),
                'ftbu_rank' => $this->string(64)->notNull(),
                'description' => $this->text(),
                'color' => $this->string(16),
                'level' => $this->integer()->notNull()->defaultValue(10),
                'enabled' => $this->boolean()->notNull()->defaultValue(1),
                'sort' => $this->integer()->notNull()->defaultValue(100),
                'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            ],
            $this->options
        );

        $this->createIndex(
            'uq_privilege_group_code',
            '{{%privilege_groups}}',
            'code',
            true
        );

        $this->createIndex(
            'uq_privilege_group_ftbu_rank',
            '{{%privilege_groups}}',
            'ftbu_rank',
            true
        );

        $this->addColumn(
            '{{%shop_products}}',
            'product_type',
            $this->string(32)
                ->notNull()
                ->defaultValue('item')
                ->after('description')
        );

        $this->addColumn(
            '{{%shop_products}}',
            'privilege_group_id',
            $this->integer()
                ->null()
                ->after('product_type')
        );

        $this->addColumn(
            '{{%shop_products}}',
            'duration_days',
            $this->integer()
                ->unsigned()
                ->null()
                ->after('privilege_group_id')
        );

        $this->createIndex(
            'idx_shop_product_privilege',
            '{{%shop_products}}',
            'privilege_group_id'
        );

        $this->addForeignKey(
            'fk_shop_product_privilege',
            '{{%shop_products}}',
            'privilege_group_id',
            '{{%privilege_groups}}',
            'id',
            'SET NULL'
        );

        $this->createTable(
            '{{%user_privileges}}',
            [
                'id' => $this->bigPrimaryKey()->unsigned(),
                'user_id' => $this->bigInteger()->unsigned()->notNull(),
                'server_id' => $this->integer()->notNull(),
                'privilege_group_id' => $this->integer()->notNull(),
                'purchase_id' => $this->bigInteger()->unsigned(),
                'status' => $this->string(32)->notNull()->defaultValue('active'),
                'source' => $this->string(32)->notNull()->defaultValue('purchase'),
                'active_from' => $this->dateTime()->notNull(),
                'active_to' => $this->dateTime()->notNull(),
                'created_at' => $this->dateTime()->notNull(),
                'updated_at' => $this->dateTime()->notNull(),
            ],
            $this->options
        );

        $this->createIndex(
            'uq_user_privilege_user_server',
            '{{%user_privileges}}',
            ['user_id', 'server_id'],
            true
        );

        $this->createIndex(
            'idx_user_privilege_expire',
            '{{%user_privileges}}',
            ['status', 'active_to', 'id']
        );

        $this->addForeignKey(
            'fk_user_privilege_user',
            '{{%user_privileges}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_user_privilege_server',
            '{{%user_privileges}}',
            'server_id',
            '{{%servers}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_user_privilege_group',
            '{{%user_privileges}}',
            'privilege_group_id',
            '{{%privilege_groups}}',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk_user_privilege_purchase',
            '{{%user_privileges}}',
            'purchase_id',
            '{{%shop_purchases}}',
            'id',
            'SET NULL'
        );

        $this->createTable(
            '{{%privilege_history}}',
            [
                'id' => $this->bigPrimaryKey()->unsigned(),
                'user_privilege_id' => $this->bigInteger()->unsigned(),
                'user_id' => $this->bigInteger()->unsigned()->notNull(),
                'server_id' => $this->integer()->notNull(),
                'purchase_id' => $this->bigInteger()->unsigned(),
                'action' => $this->string(32)->notNull(),
                'from_group_id' => $this->integer(),
                'to_group_id' => $this->integer(),
                'old_active_to' => $this->dateTime(),
                'new_active_to' => $this->dateTime(),
                'source' => $this->string(32)->notNull(),
                'metadata_json' => $this->text(),
                'created_at' => $this->dateTime()->notNull(),
            ],
            $this->options
        );

        $this->createIndex(
            'idx_privilege_history_user',
            '{{%privilege_history}}',
            ['user_id', 'id']
        );

        $this->createIndex(
            'idx_privilege_history_privilege',
            '{{%privilege_history}}',
            ['user_privilege_id', 'id']
        );

        $this->addForeignKey(
            'fk_privilege_history_privilege',
            '{{%privilege_history}}',
            'user_privilege_id',
            '{{%user_privileges}}',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk_privilege_history_user',
            '{{%privilege_history}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_privilege_history_server',
            '{{%privilege_history}}',
            'server_id',
            '{{%servers}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_privilege_history_purchase',
            '{{%privilege_history}}',
            'purchase_id',
            '{{%shop_purchases}}',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk_privilege_history_from_group',
            '{{%privilege_history}}',
            'from_group_id',
            '{{%privilege_groups}}',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk_privilege_history_to_group',
            '{{%privilege_history}}',
            'to_group_id',
            '{{%privilege_groups}}',
            'id',
            'SET NULL'
        );

        foreach(
            [
                [
                    'vip',
                    'VIP',
                    'vip',
                    'Базовый платный статус.',
                    '#39d98a',
                    20,
                    10,
                ],
                [
                    'premium',
                    'Premium',
                    'premium',
                    'Расширенный статус.',
                    '#5bc0ff',
                    30,
                    20,
                ],
                [
                    'elite',
                    'Elite',
                    'elite',
                    'Высокий уровень привилегий.',
                    '#f7b955',
                    40,
                    30,
                ],
                [
                    'legend',
                    'Legend',
                    'legend',
                    'Максимальный пользовательский статус.',
                    '#d98cff',
                    50,
                    40,
                ],
            ]
            as [
                $code,
                $name,
                $rank,
                $description,
                $color,
                $level,
                $sort
            ]
        )
        {
            $this->insert(
                '{{%privilege_groups}}',
                [
                    'code' => $code,
                    'name' => $name,
                    'ftbu_rank' => $rank,
                    'description' => $description,
                    'color' => $color,
                    'level' => $level,
                    'enabled' => 1,
                    'sort' => $sort,
                ]
            );
        }

        $now = time();

        $this->execute(
            "
            INSERT IGNORE INTO {{%auth_item}}
                (
                    name,
                    type,
                    description,
                    created_at,
                    updated_at
                )
            VALUES
                (
                    'managePrivileges',
                    2,
                    'Manage Minecraft privileges',
                    {$now},
                    {$now}
                )
            "
        );

        $this->execute(
            "
            INSERT IGNORE INTO {{%auth_item_child}}
                (
                    parent,
                    child
                )
            VALUES
                (
                    'admin',
                    'managePrivileges'
                )
            "
        );
    }

    public function safeDown(): void
    {
        $this->delete(
            '{{%auth_item_child}}',
            [
                'parent' => 'admin',
                'child' => 'managePrivileges',
            ]
        );

        $this->delete(
            '{{%auth_item}}',
            [
                'name' => 'managePrivileges',
            ]
        );

        $this->dropTable(
            '{{%privilege_history}}'
        );

        $this->dropTable(
            '{{%user_privileges}}'
        );

        $this->dropForeignKey(
            'fk_shop_product_privilege',
            '{{%shop_products}}'
        );

        $this->dropIndex(
            'idx_shop_product_privilege',
            '{{%shop_products}}'
        );

        $this->dropColumn(
            '{{%shop_products}}',
            'duration_days'
        );

        $this->dropColumn(
            '{{%shop_products}}',
            'privilege_group_id'
        );

        $this->dropColumn(
            '{{%shop_products}}',
            'product_type'
        );

        $this->dropTable(
            '{{%privilege_groups}}'
        );
    }
}
