<?php

declare(strict_types=1);

use yii\db\Migration;

final class m261009_000003_add_minecraft_operator_permission extends Migration
{
    public function safeUp(): void
    {
        $now = time();

        $this->execute(
            'INSERT IGNORE INTO {{%auth_item}} '
            . '(name,type,description,created_at,updated_at) '
            . 'VALUES (:name,2,:description,:created,:updated)',
            [
                ':name' => 'manageMinecraftOperators',
                ':description' => 'Grant and revoke Minecraft operator status',
                ':created' => $now,
                ':updated' => $now,
            ]
        );

        $this->execute(
            'INSERT IGNORE INTO {{%auth_item_child}} (parent,child) '
            . 'VALUES (\'admin\', :child)',
            [
                ':child' => 'manageMinecraftOperators',
            ]
        );
    }

    public function safeDown(): void
    {
        $this->delete('{{%auth_item_child}}', [
            'parent' => 'admin',
            'child' => 'manageMinecraftOperators',
        ]);

        $this->delete('{{%auth_item}}', [
            'name' => 'manageMinecraftOperators',
        ]);
    }
}
