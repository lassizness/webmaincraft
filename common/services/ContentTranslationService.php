<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    News,
    NewsTranslation,
    Page,
    PageTranslation
};
use Yii;

final class ContentTranslationService
{
    public static function applyPage(Page $page): Page
    {
        $lang = Yii::$app->language;

        if($lang === 'ru')
        {
            return $page;
        }

        $translation = PageTranslation::findOne([
            'page_id' => $page->id,
            'language' => $lang,
        ]);

        if(!$translation)
        {
            return $page;
        }

        $page->title = $translation->title;
        $page->body = $translation->body;
        $page->meta_title = $translation->meta_title;
        $page->meta_description = $translation->meta_description;

        return $page;
    }

    public static function applyNews(News $news): News
    {
        $lang = Yii::$app->language;

        if($lang === 'ru')
        {
            return $news;
        }

        $translation = NewsTranslation::findOne([
            'news_id' => $news->id,
            'language' => $lang,
        ]);

        if(!$translation)
        {
            return $news;
        }

        $news->title = $translation->title;
        $news->body = $translation->body;
        $news->excerpt = $translation->excerpt;

        return $news;
    }
}
