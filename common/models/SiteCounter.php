<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class SiteCounter extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%site_counters}}';
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],

            [
                ['head_code', 'body_start_code', 'body_end_code'],
                'string'
            ],

            [['enabled'], 'boolean'],
            [['sort'], 'integer'],

            [['name'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'Название',
            'head_code' => 'Код в <head>',
            'body_start_code' => 'Код после <body>',
            'body_end_code' => 'Код перед </body>',
            'enabled' => 'Включён',
            'sort' => 'Сортировка',
        ];
    }
}
