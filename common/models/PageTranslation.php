<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class PageTranslation extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%page_translations}}';
    }

    public function rules(): array
    {
        return [
            [['page_id', 'language', 'title', 'body'], 'required'],
            [['page_id'], 'integer'],
            [['body'], 'string'],
            [['language'], 'string', 'max' => 8],
            [['title', 'meta_title', 'meta_description'], 'string', 'max' => 255],
        ];
    }
}
