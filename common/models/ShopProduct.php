<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class ShopProduct extends ActiveRecord
{
    public const TYPE_ITEM = 'item';
    public const TYPE_PRIVILEGE = 'privilege';

    public static function tableName(): string
    {
        return '{{%shop_products}}';
    }

    public function rules(): array
    {
        return [
            [
                [
                    'name',
                    'slug',
                    'price',
                    'product_type',
                ],
                'required',
            ],
            [
                [
                    'description',
                    'delivery_payload',
                ],
                'string',
            ],
            [
                [
                    'category_id',
                    'enabled',
                    'sort',
                    'server_id',
                    'privilege_group_id',
                    'duration_days',
                ],
                'integer',
            ],
            [
                [
                    'price',
                ],
                'number',
                'min' => 0,
            ],
            [
                'product_type',
                'in',
                'range' => [
                    self::TYPE_ITEM,
                    self::TYPE_PRIVILEGE,
                ],
            ],
            [
                'delivery_type',
                'in',
                'range' => [
                    'none',
                    'rcon_command',
                    'permission',
                    'cosmetic',
                ],
            ],
            [
                'slug',
                'match',
                'pattern' =>
                    '/^[a-z0-9-]+$/',
            ],
            [
                'slug',
                'unique',
            ],
            [
                'server_id',
                'required',
                'when' => static fn(self $model): bool =>
                    $model->product_type
                        === self::TYPE_PRIVILEGE,
            ],
            [
                'privilege_group_id',
                'required',
                'when' => static fn(self $model): bool =>
                    $model->product_type
                        === self::TYPE_PRIVILEGE,
            ],
            [
                'duration_days',
                'required',
                'when' => static fn(self $model): bool =>
                    $model->product_type
                        === self::TYPE_PRIVILEGE,
            ],
            [
                'duration_days',
                'integer',
                'min' => 1,
                'max' => 3650,
            ],
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(
            ShopCategory::class,
            [
                'id' => 'category_id',
            ]
        );
    }

    public function getPrivilegeGroup()
    {
        return $this->hasOne(
            PrivilegeGroup::class,
            [
                'id' => 'privilege_group_id',
            ]
        );
    }
}
