<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260915_000002_topg_vote_by_username extends Migration
{
    public function safeUp(): void
    {
        $this->update(
            '{{%vote_services}}',
            [
                'vote_url_template' => 'https://topg.org/ru/minecraft-servers/server-685225-{username}#vote',
            ],
            ['code' => 'topg']
        );
    }

    public function safeDown(): void
    {
        $this->update(
            '{{%vote_services}}',
            [
                'vote_url_template' => 'https://topg.org/ru/minecraft-servers/server-685225-{token}#vote',
            ],
            ['code' => 'topg']
        );
    }
}
