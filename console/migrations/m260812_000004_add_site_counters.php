<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260812_000004_add_site_counters extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%site_counters}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),

            'head_code' => $this->text(),
            'body_start_code' => $this->text(),
            'body_end_code' => $this->text(),

            'enabled' => $this->boolean()->notNull()->defaultValue(1),
            'sort' => $this->integer()->notNull()->defaultValue(100),

            'created_at' => $this->dateTime()
                ->notNull()
                ->defaultExpression('CURRENT_TIMESTAMP'),

            'updated_at' => $this->dateTime()
                ->notNull()
                ->defaultExpression('CURRENT_TIMESTAMP')
                ->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex(
            'idx_site_counters_enabled_sort',
            '{{%site_counters}}',
            ['enabled', 'sort']
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%site_counters}}');
    }
}
