<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260810_000003_seed_privilege_shop extends Migration
{
    public function safeUp(): void
    {
        // Категория магазина
        $this->execute("
            INSERT INTO {{%shop_categories}}
                (name, slug, enabled, sort)
            VALUES
                ('Привилегии', 'privileges', 1, 10)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                enabled = 1,
                sort = VALUES(sort)
        ");

        $categoryId = (int)$this->db
            ->createCommand("
                SELECT id
                FROM {{%shop_categories}}
                WHERE slug = 'privileges'
                LIMIT 1
            ")
            ->queryScalar();

        $serverId = (int)$this->db
            ->createCommand("
                SELECT id
                FROM {{%servers}}
                WHERE code = 'HiveTech'
                LIMIT 1
            ")
            ->queryScalar();

        if($serverId <= 0)
        {
            throw new RuntimeException('Сервер HiveTech не найден');
        }

        $products = [
            [
                'group' => 'vip',
                'name' => 'VIP на 30 дней',
                'slug' => 'vip-30-days',
                'description' => 'Статус VIP на 30 дней.',
                'price' => 199.00,
                'days' => 30,
                'sort' => 10,
            ],
            [
                'group' => 'premium',
                'name' => 'Premium на 30 дней',
                'slug' => 'premium-30-days',
                'description' => 'Статус Premium на 30 дней.',
                'price' => 349.00,
                'days' => 30,
                'sort' => 20,
            ],
            [
                'group' => 'elite',
                'name' => 'Elite на 30 дней',
                'slug' => 'elite-30-days',
                'description' => 'Статус Elite на 30 дней.',
                'price' => 549.00,
                'days' => 30,
                'sort' => 30,
            ],
            [
                'group' => 'legend',
                'name' => 'Legend на 30 дней',
                'slug' => 'legend-30-days',
                'description' => 'Статус Legend на 30 дней.',
                'price' => 799.00,
                'days' => 30,
                'sort' => 40,
            ],
        ];

        foreach($products as $product)
        {
            $groupId = (int)$this->db
                ->createCommand("
                    SELECT id
                    FROM {{%privilege_groups}}
                    WHERE code = :code
                    LIMIT 1
                ", [
                    ':code' => $product['group'],
                ])
                ->queryScalar();

            if($groupId <= 0)
            {
                throw new RuntimeException(
                    'Privilege group not found: ' . $product['group']
                );
            }

            $this->execute("
                INSERT INTO {{%shop_products}}
                (
                    category_id,
                    server_id,
                    name,
                    slug,
                    description,
                    product_type,
                    privilege_group_id,
                    duration_days,
                    price,
                    delivery_type,
                    delivery_payload,
                    enabled,
                    sort
                )
                VALUES
                (
                    :category_id,
                    :server_id,
                    :name,
                    :slug,
                    :description,
                    'privilege',
                    :group_id,
                    :duration_days,
                    :price,
                    'none',
                    NULL,
                    1,
                    :sort
                )
                ON DUPLICATE KEY UPDATE
                    category_id = VALUES(category_id),
                    server_id = VALUES(server_id),
                    name = VALUES(name),
                    description = VALUES(description),
                    product_type = 'privilege',
                    privilege_group_id = VALUES(privilege_group_id),
                    duration_days = VALUES(duration_days),
                    price = VALUES(price),
                    delivery_type = 'none',
                    delivery_payload = NULL,
                    enabled = 1,
                    sort = VALUES(sort)
            ", [
                ':category_id' => $categoryId,
                ':server_id' => $serverId,
                ':name' => $product['name'],
                ':slug' => $product['slug'],
                ':description' => $product['description'],
                ':group_id' => $groupId,
                ':duration_days' => $product['days'],
                ':price' => $product['price'],
                ':sort' => $product['sort'],
            ]);
        }
    }

    public function safeDown(): void
    {
        $this->delete('{{%shop_products}}', [
            'slug' => [
                'vip-30-days',
                'premium-30-days',
                'elite-30-days',
                'legend-30-days',
            ],
        ]);

        // Категорию специально не удаляем:
        // в ней к этому моменту уже могут появиться другие товары.
    }
}
