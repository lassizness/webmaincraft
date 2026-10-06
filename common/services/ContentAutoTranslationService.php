<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    News,
    NewsTranslation,
    Page,
    PageTranslation
};

final class ContentAutoTranslationService
{
    private const LANGUAGES = ['en', 'de'];

    private LibreTranslationService $translator;

    public function __construct()
    {
        $this->translator = new LibreTranslationService();
    }

    /**
     * @return string[] Ошибки по языкам. Пустой массив = всё успешно.
     */
    public function translatePage(Page $page): array
    {
        $errors = [];

        foreach(self::LANGUAGES as $language)
        {
            try
            {
                $translation = PageTranslation::findOne([
                    'page_id' => $page->id,
                    'language' => $language,
                ]);

                if(!$translation)
                {
                    $translation = new PageTranslation([
                        'page_id' => $page->id,
                        'language' => $language,
                    ]);
                }

                $translation->title = $this->translator->translate(
                    (string)$page->title,
                    $language
                );

                $translation->body = $this->translator->translate(
                    (string)$page->body,
                    $language,
                    'html'
                );

                $translation->meta_title =
                    trim((string)$page->meta_title) !== ''
                        ? $this->translator->translate(
                            (string)$page->meta_title,
                            $language
                        )
                        : null;

                $translation->meta_description =
                    trim((string)$page->meta_description) !== ''
                        ? $this->translator->translate(
                            (string)$page->meta_description,
                            $language
                        )
                        : null;

                $translation->save(false);
            }
            catch(\Throwable $e)
            {
                $errors[] = strtoupper($language) . ': ' . $e->getMessage();
            }
        }

        return $errors;
    }

    /**
     * @return string[] Ошибки по языкам. Пустой массив = всё успешно.
     */
    public function translateNews(News $news): array
    {
        $errors = [];

        foreach(self::LANGUAGES as $language)
        {
            try
            {
                $translation = NewsTranslation::findOne([
                    'news_id' => $news->id,
                    'language' => $language,
                ]);

                if(!$translation)
                {
                    $translation = new NewsTranslation([
                        'news_id' => $news->id,
                        'language' => $language,
                    ]);
                }

                $translation->title = $this->translator->translate(
                    (string)$news->title,
                    $language
                );

                $translation->excerpt =
                    trim((string)$news->excerpt) !== ''
                        ? $this->translator->translate(
                            (string)$news->excerpt,
                            $language
                        )
                        : null;

                $translation->body = $this->translator->translate(
                    (string)$news->body,
                    $language,
                    'html'
                );

                $translation->save(false);
            }
            catch(\Throwable $e)
            {
                $errors[] = strtoupper($language) . ': ' . $e->getMessage();
            }
        }

        return $errors;
    }
}
