<?php

declare(strict_types=1);

use yii\db\Migration;

final class m261006_000001_add_user_language extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn(
            '{{%users}}',
            'language',
            $this->string(2)->notNull()->defaultValue('ru')->after('email_verified')
        );
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%users}}', 'language');
    }
}
