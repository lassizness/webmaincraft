<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\components\LanguageBootstrap;
use common\models\{
    News,
    Page,
    Setting
};
use common\services\SeoService;
use frontend\components\Controller;
use Yii;
use yii\db\Query;
use yii\web\Response;

final class SeoController extends Controller
{
    public function actionRobots(): Response
    {
        $baseUrl = rtrim(
            (string)Yii::$app->params['appUrl'],
            '/'
        );

        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /internal/',
            'Disallow: /account/',
            'Disallow: /support/',
            'Disallow: /wallet',
            'Disallow: /skins',
            'Disallow: /texture/',
            'Disallow: /forum/sso',
            'Disallow: /language/',
            'Disallow: /*/callback',
            '',
            'Sitemap: ' . $baseUrl . '/sitemap.xml',
            '',
        ]);

        return $this->rawResponse(
            $body,
            'text/plain; charset=UTF-8'
        );
    }

    public function actionSitemap(): Response
    {
        $entries = [];

        $this->addGeneric(
            $entries,
            '/',
            null
        );

        $this->addGeneric(
            $entries,
            '/news',
            $this->latestNewsDate()
        );

        $this->addGeneric(
            $entries,
            '/servers',
            null
        );

        if(
            (string)Setting::value(
                'shop.enabled',
                '1'
            ) === '1'
        )
        {
            $this->addGeneric(
                $entries,
                '/shop',
                null
            );
        }

        $this->addGeneric(
            $entries,
            '/vote',
            null
        );

        if(
            (string)Setting::value(
                'ratings.public_enabled',
                '0'
            ) === '1'
        )
        {
            $this->addLocalized(
                $entries,
                '/ratings',
                ['ru'],
                null
            );
        }

        $pages = Page::find()
            ->where(['published' => 1])
            ->orderBy([
                'sort' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->all();

        foreach($pages as $page)
        {
            $this->addLocalized(
                $entries,
                '/page/' . rawurlencode(
                    (string)$page->slug
                ),
                SeoService::contentLanguages(
                    'page',
                    (int)$page->id
                ),
                null
            );
        }

        $news = News::find()
            ->where(['published' => 1])
            ->andWhere([
                'or',
                ['published_at' => null],
                [
                    '<=',
                    'published_at',
                    date('Y-m-d H:i:s'),
                ],
            ])
            ->orderBy([
                'published_at' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->all();

        foreach($news as $item)
        {
            $this->addLocalized(
                $entries,
                '/news/' . rawurlencode(
                    (string)$item->slug
                ),
                SeoService::contentLanguages(
                    'news',
                    (int)$item->id
                ),
                $item->published_at
                    ?: $item->created_at
            );
        }

        $xml = $this->renderSitemap($entries);

        return $this->rawResponse(
            $xml,
            'application/xml; charset=UTF-8'
        );
    }

    private function addGeneric(
        array &$entries,
        string $path,
        ?string $lastModified
    ): void {
        $this->addLocalized(
            $entries,
            $path,
            array_keys(
                LanguageBootstrap::LANGUAGES
            ),
            $lastModified
        );
    }

    private function addLocalized(
        array &$entries,
        string $path,
        array $languages,
        ?string $lastModified
    ): void {
        $languages = array_values(
            array_filter(
                array_unique($languages),
                static fn(string $language): bool =>
                    isset(
                        LanguageBootstrap::LANGUAGES[
                            $language
                        ]
                    )
            )
        );

        if(!$languages)
        {
            $languages = ['ru'];
        }

        $alternates = [];

        foreach($languages as $language)
        {
            $alternates[$language] =
                SeoService::absolutePath(
                    $path,
                    $language
                );
        }

        $defaultLanguage =
            isset($alternates['en'])
                ? 'en'
                : (
                    array_key_first($alternates)
                    ?: 'ru'
                );

        $alternates['x-default'] =
            $alternates[$defaultLanguage];

        foreach($languages as $language)
        {
            $entries[] = [
                'loc' => $alternates[$language],
                'lastmod' =>
                    $this->formatLastModified(
                        $lastModified
                    ),
                'alternates' => $alternates,
            ];
        }
    }

    private function renderSitemap(
        array $entries
    ): string {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">',
        ];

        foreach($entries as $entry)
        {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'
                . $this->xml(
                    (string)$entry['loc']
                )
                . '</loc>';

            if($entry['lastmod'] !== null)
            {
                $lines[] = '    <lastmod>'
                    . $this->xml(
                        (string)$entry['lastmod']
                    )
                    . '</lastmod>';
            }

            foreach(
                $entry['alternates']
                as $language => $url
            )
            {
                $lines[] =
                    '    <xhtml:link rel="alternate" hreflang="'
                    . $this->xml($language)
                    . '" href="'
                    . $this->xml($url)
                    . '" />';
            }

            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines) . "\n";
    }

    private function latestNewsDate(): ?string
    {
        $value = (new Query())
            ->from('{{%news}}')
            ->where(['published' => 1])
            ->andWhere([
                'or',
                ['published_at' => null],
                [
                    '<=',
                    'published_at',
                    date('Y-m-d H:i:s'),
                ],
            ])
            ->max(
                'COALESCE(published_at, created_at)'
            );

        return is_string($value)
            ? $value
            : null;
    }

    private function formatLastModified(
        ?string $value
    ): ?string {
        if($value === null || $value === '')
        {
            return null;
        }

        $time = strtotime($value);

        return $time === false
            ? null
            : date('Y-m-d', $time);
    }

    private function xml(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_XML1 | ENT_QUOTES,
            'UTF-8'
        );
    }

    private function rawResponse(
        string $body,
        string $contentType
    ): Response {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set(
            'Content-Type',
            $contentType
        );
        $response->headers->set(
            'Cache-Control',
            'public, max-age=300'
        );
        $response->data = $body;

        return $response;
    }
}
