<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\db\Query;

final class m261004_000001_add_topcraft_voting extends Migration
{
    public function safeUp(): void
    {
        $table = (string)$this->db->tablePrefix . 'vote_services';
        $schema = $this->db->schema->getTableSchema($table, true);
        if ($schema === null) {
            throw new RuntimeException('vote_services table does not exist. Apply voting migrations first.');
        }

        if ($schema->getColumn('callback_secret') === null) {
            $this->addColumn('{{%vote_services}}', 'callback_secret', $this->string(255)->null()->after('reward_cooldown_seconds'));
        }
        if ($schema->getColumn('banner_url') === null) {
            $this->addColumn('{{%vote_services}}', 'banner_url', $this->string(512)->null()->after('callback_secret'));
        }
        if ($schema->getColumn('show_on_home') === null) {
            $this->addColumn('{{%vote_services}}', 'show_on_home', $this->boolean()->notNull()->defaultValue(0)->after('banner_url'));
        }

        $exists = (new Query())
            ->from('{{%vote_services}}')
            ->where(['code' => 'topcraft'])
            ->exists($this->db);

        if (!$exists) {
            $this->insert('{{%vote_services}}', [
                'code' => 'topcraft',
                'name' => 'TopCraft',
                'driver' => 'topcraft',
                'vote_url_template' => 'https://topcraft.club/servers/13035/',
                'reward_amount' => 15.00,
                'reward_cooldown_seconds' => 86400,
                'callback_secret' => null,
                'banner_url' => 'https://topcraft.club/media/projects/13035/tops.png',
                'show_on_home' => 1,
                // Keep disabled until the TopCraft secret is copied into the admin form.
                'enabled' => 0,
                'sort' => 20,
                'config' => null,
            ]);
        }
    }

    public function safeDown(): void
    {
        $this->delete('{{%vote_services}}', ['code' => 'topcraft']);

        $table = (string)$this->db->tablePrefix . 'vote_services';
        $schema = $this->db->schema->getTableSchema($table, true);
        if ($schema === null) {
            return;
        }

        if ($schema->getColumn('show_on_home') !== null) {
            $this->dropColumn('{{%vote_services}}', 'show_on_home');
        }
        if ($schema->getColumn('banner_url') !== null) {
            $this->dropColumn('{{%vote_services}}', 'banner_url');
        }
        if ($schema->getColumn('callback_secret') !== null) {
            $this->dropColumn('{{%vote_services}}', 'callback_secret');
        }
    }
}
