<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class ShopCategory extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%shop_categories}}';
    }

    public function rules(): array
    {
        return [
            [['name', 'slug'], 'required'],
            [['description'], 'string'],
            [['enabled', 'sort'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 128],
            ['slug', 'match', 'pattern' => '/^[a-z0-9-]+$/'],
            ['slug', 'unique'],
        ];
    }

    public function getProducts()
    {
        return $this->hasMany(
            ShopProduct::class,
            ['category_id' => 'id']
        );
    }
}
