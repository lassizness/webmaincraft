<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class NewsTranslation extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%news_translations}}';
    }

    public function rules(): array
    {
        return [
            [['news_id', 'language', 'title', 'body'], 'required'],
            [['news_id'], 'integer'],
            [['body', 'excerpt'], 'string'],
            [['language'], 'string', 'max' => 8],
            [['title'], 'string', 'max' => 255],
        ];
    }
}
