<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260818_000010_add_content_translations extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%page_translations}}', [
            'id' => $this->primaryKey(),
            'page_id' => $this->integer()->notNull(),
            'language' => $this->string(8)->notNull(),
            'title' => $this->string(255)->notNull(),
            'body' => $this->text()->notNull(),
            'meta_title' => $this->string(255),
            'meta_description' => $this->string(255),
            'updated_at' => $this->dateTime()
                ->notNull()
                ->defaultExpression('CURRENT_TIMESTAMP')
                ->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex(
            'uq_page_translations_page_language',
            '{{%page_translations}}',
            ['page_id', 'language'],
            true
        );

        $this->addForeignKey(
            'fk_page_translations_page',
            '{{%page_translations}}',
            'page_id',
            '{{%pages}}',
            'id',
            'CASCADE',
            'CASCADE'
        );


        $this->createTable('{{%news_translations}}', [
            'id' => $this->primaryKey(),
            'news_id' => $this->integer()->notNull(),
            'language' => $this->string(8)->notNull(),
            'title' => $this->string(255)->notNull(),
            'excerpt' => $this->text(),
            'body' => $this->text()->notNull(),
            'updated_at' => $this->dateTime()
                ->notNull()
                ->defaultExpression('CURRENT_TIMESTAMP')
                ->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex(
            'uq_news_translations_news_language',
            '{{%news_translations}}',
            ['news_id', 'language'],
            true
        );

        $this->addForeignKey(
            'fk_news_translations_news',
            '{{%news_translations}}',
            'news_id',
            '{{%news}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%news_translations}}');
        $this->dropTable('{{%page_translations}}');
    }
}
