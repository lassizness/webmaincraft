<?php

declare(strict_types=1);

use yii\db\Migration;

final class m261007_000002_make_email_verification_nonblocking extends Migration
{
    public function safeUp(): void
    {
        $this->update(
            '{{%users}}',
            ['status' => 'active'],
            ['status' => 'pending']
        );

        $this->execute(
            "ALTER TABLE {{%users}} "
            . "MODIFY status enum('pending','active','blocked') "
            . "NOT NULL DEFAULT 'active'"
        );
    }

    public function safeDown(): bool
    {
        echo "Email verification is independent from account status; this data migration is intentionally irreversible.\n";
        return false;
    }
}
