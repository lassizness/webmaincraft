<?php

declare(strict_types=1);

use yii\db\Migration;

final class m261007_000003_add_p0_platform_ops extends Migration
{
    private string $options = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp(): void
    {
        if(!$this->db->schema->getTableSchema('{{%privilege_notification_log}}', true))
        {
            $this->createTable('{{%privilege_notification_log}}', [
                'id' => $this->bigPrimaryKey()->unsigned(),
                'user_privilege_id' => $this->bigInteger()->unsigned()->notNull(),
                'notice_type' => $this->string(16)->notNull(),
                'active_to' => $this->dateTime()->notNull(),
                'sent_at' => $this->dateTime()->notNull(),
            ], $this->options);

            $this->createIndex(
                'uq_privilege_notice_once',
                '{{%privilege_notification_log}}',
                ['user_privilege_id', 'notice_type', 'active_to'],
                true
            );

            $this->addForeignKey(
                'fk_privilege_notice_privilege',
                '{{%privilege_notification_log}}',
                'user_privilege_id',
                '{{%user_privileges}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }

        $now = time();

        foreach([
            ['manageDelivery', 'Manage delivery queue'],
            ['manageHwid', 'Manage launcher HWID'],
        ] as [$name, $description])
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

        foreach([
            ['payments.paypalych.enabled', '0'],
            ['payments.paypalych.shop_id', ''],
            ['payments.paypalych.base_url', 'https://pally.info/api/v1'],
        ] as [$key, $value])
        {
            $this->execute(
                'INSERT IGNORE INTO {{%settings}} (`key`, value) VALUES (:key, :value)',
                [':key' => $key, ':value' => $value]
            );
        }
    }

    public function safeDown(): void
    {
        $this->delete('{{%auth_item_child}}', [
            'parent' => 'admin',
            'child' => ['manageDelivery', 'manageHwid'],
        ]);
        $this->delete('{{%auth_item}}', [
            'name' => ['manageDelivery', 'manageHwid'],
        ]);
        $this->delete('{{%settings}}', [
            'key' => [
                'payments.paypalych.enabled',
                'payments.paypalych.shop_id',
                'payments.paypalych.base_url',
            ],
        ]);

        if($this->db->schema->getTableSchema('{{%privilege_notification_log}}', true))
        {
            $this->dropTable('{{%privilege_notification_log}}');
        }
    }
}
