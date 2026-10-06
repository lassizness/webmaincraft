<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\db\Query;

final class m260915_000001_add_voting extends Migration
{
    private string $options = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp(): void
    {
        // MariaDB/MySQL DDL is not fully transactional. If an earlier run stopped
        // after CREATE TABLE/INDEX (for example on a missing REFERENCES privilege),
        // allow the same migration to finish instead of failing with "table exists".
        if (!$this->tableExists('vote_services')) {
            $this->createTable('{{%vote_services}}', [
                'id' => $this->primaryKey()->unsigned(),
                'code' => 'varchar(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL',
                'name' => $this->string(128)->notNull(),
                'driver' => 'varchar(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL',
                'vote_url_template' => $this->string(512)->notNull(),
                'reward_amount' => $this->decimal(14, 2)->notNull()->defaultValue(15),
                'reward_cooldown_seconds' => $this->integer()->unsigned()->notNull()->defaultValue(86400),
                'enabled' => $this->boolean()->notNull()->defaultValue(1),
                'sort' => $this->integer()->notNull()->defaultValue(100),
                'config' => $this->text(),
                'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            ], $this->options);

            $this->createIndex('uq_vote_services_code', '{{%vote_services}}', 'code', true);
            $this->createIndex('idx_vote_services_enabled_sort', '{{%vote_services}}', ['enabled', 'sort']);
        }

        if (!$this->tableExists('votes')) {
            $this->createTable('{{%votes}}', [
                'id' => $this->bigPrimaryKey()->unsigned(),
                'service_id' => $this->integer()->unsigned()->notNull(),
                'user_id' => $this->bigInteger()->unsigned()->notNull(),
                'vote_date' => $this->date()->notNull(),
                'voter_ip' => $this->string(45),
                'callback_ip' => $this->string(45),
                'provider_vote_id' => $this->string(128),
                'reward_amount' => $this->decimal(14, 2)->notNull(),
                'rewarded_at' => $this->dateTime(),
                'callback_payload' => $this->text(),
                'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ], $this->options);

            $this->createIndex(
                'idx_votes_service_user_rewarded',
                '{{%votes}}',
                ['service_id', 'user_id', 'rewarded_at']
            );
            $this->createIndex(
                'uq_votes_service_provider_vote',
                '{{%votes}}',
                ['service_id', 'provider_vote_id'],
                true
            );
            $this->createIndex('idx_votes_user_created', '{{%votes}}', ['user_id', 'created_at']);
            $this->createIndex('idx_votes_service_created', '{{%votes}}', ['service_id', 'created_at']);
        }

        // Foreign keys are DDL and must be created by the temporary migration user.
        // The runtime web user only needs DML rights on these tables. These checks also
        // let the migration recover from a previous partial MariaDB/MySQL DDL run.
        if (!$this->foreignKeyExists('fk_votes_service')) {
            $this->addForeignKey(
                'fk_votes_service',
                '{{%votes}}',
                'service_id',
                '{{%vote_services}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }
        if (!$this->foreignKeyExists('fk_votes_user')) {
            $this->addForeignKey(
                'fk_votes_user',
                '{{%votes}}',
                'user_id',
                '{{%users}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }

        $topgExists = (new Query())
            ->from('{{%vote_services}}')
            ->where(['code' => 'topg'])
            ->exists($this->db);

        if (!$topgExists) {
            $this->insert('{{%vote_services}}', [
                'code' => 'topg',
                'name' => 'TopG',
                'driver' => 'topg',
                'vote_url_template' => 'https://topg.org/ru/minecraft-servers/server-685225-{token}#vote',
                'reward_amount' => 15.00,
                'reward_cooldown_seconds' => 86400,
                'enabled' => 1,
                'sort' => 10,
                'config' => json_encode([
                    'callback_host' => 'monitor.topg.org',
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    public function safeDown(): void
    {
        if ($this->tableExists('votes')) {
            $this->dropTable('{{%votes}}');
        }
        if ($this->tableExists('vote_services')) {
            $this->dropTable('{{%vote_services}}');
        }
    }

    private function tableExists(string $table): bool
    {
        $table = (string)$this->db->tablePrefix . $table;

        return (bool)$this->db->createCommand(
            'SELECT 1 FROM information_schema.TABLES '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1',
            [':table' => $table]
        )->queryScalar();
    }

    private function foreignKeyExists(string $constraint): bool
    {
        return (bool)$this->db->createCommand(
            'SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS '
            . 'WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = :constraint LIMIT 1',
            [':constraint' => $constraint]
        )->queryScalar();
    }
}
