<?php

declare(strict_types=1);

namespace console\controllers;

use common\models\{
    News,
    NewsTranslation,
    Page,
    PageTranslation
};
use common\services\LibreTranslationService;
use yii\console\Controller;

final class TranslateController extends Controller
{
    public function actionContent(
        string $languages = 'en,de'
    ): int {
        $targets = array_values(array_filter(
            array_map('trim', explode(',', $languages))
        ));

        $translator = new LibreTranslationService();

        foreach($targets as $lang)
        {
            if(!in_array($lang, ['en', 'de'], true))
            {
                $this->stderr(
                    "Unsupported language: {$lang}\n"
                );

                continue;
            }

            $this->stdout(
                "=== {$lang} ===\n"
            );

            foreach(Page::find()->all() as $page)
            {
                $this->stdout(
                    "PAGE {$page->slug} ... "
                );

                $translation = PageTranslation::findOne([
                    'page_id' => $page->id,
                    'language' => $lang,
                ]);

                if(!$translation)
                {
                    $translation = new PageTranslation([
                        'page_id' => $page->id,
                        'language' => $lang,
                    ]);
                }

                $translation->title = $translator->translate(
                    $page->title,
                    $lang
                );

                $translation->body = $translator->translate(
                    $page->body,
                    $lang,
                    'html'
                );

                $translation->meta_title =
                    trim((string)$page->meta_title) !== ''
                        ? $translator->translate(
                            $page->meta_title,
                            $lang
                        )
                        : null;

                $translation->meta_description =
                    trim((string)$page->meta_description) !== ''
                        ? $translator->translate(
                            $page->meta_description,
                            $lang
                        )
                        : null;

                $translation->save(false);

                $this->stdout("OK\n");
            }

            foreach(News::find()->all() as $news)
            {
                $this->stdout(
                    "NEWS {$news->slug} ... "
                );

                $translation = NewsTranslation::findOne([
                    'news_id' => $news->id,
                    'language' => $lang,
                ]);

                if(!$translation)
                {
                    $translation = new NewsTranslation([
                        'news_id' => $news->id,
                        'language' => $lang,
                    ]);
                }

                $translation->title = $translator->translate(
                    $news->title,
                    $lang
                );

                $translation->excerpt =
                    trim((string)$news->excerpt) !== ''
                        ? $translator->translate(
                            $news->excerpt,
                            $lang
                        )
                        : null;

                $translation->body = $translator->translate(
                    $news->body,
                    $lang,
                    'html'
                );

                $translation->save(false);

                $this->stdout("OK\n");
            }
        }

        return self::EXIT_CODE_NORMAL;
    }
}
