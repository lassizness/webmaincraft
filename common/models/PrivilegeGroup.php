<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class PrivilegeGroup extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%privilege_groups}}';
    }

    public function rules(): array
    {
        return [
            [
                [
                    'code',
                    'name',
                    'ftbu_rank',
                    'level',
                ],
                'required',
            ],
            [
                [
                    'level',
                    'enabled',
                    'sort',
                ],
                'integer',
            ],
            [
                [
                    'description',
                ],
                'string',
            ],
            [
                [
                    'code',
                    'ftbu_rank',
                ],
                'match',
                'pattern' =>
                    '/^[a-z0-9_]+$/',
            ],
            [
                [
                    'code',
                    'ftbu_rank',
                ],
                'string',
                'max' => 64,
            ],
            [
                [
                    'name',
                ],
                'string',
                'max' => 128,
            ],
            [
                [
                    'color',
                ],
                'string',
                'max' => 16,
            ],
            [
                'color',
                'match',
                'pattern' =>
                    '/^#[0-9a-fA-F]{6}$/',
                'skipOnEmpty' => true,
            ],
            [
                'code',
                'unique',
            ],
            [
                'ftbu_rank',
                'unique',
            ],
        ];
    }

    public function getProducts()
    {
        return $this->hasMany(
            ShopProduct::class,
            [
                'privilege_group_id' => 'id',
            ]
        );
    }
}
