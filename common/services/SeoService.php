<?php

declare(strict_types=1);

namespace common\services;

use common\components\LanguageBootstrap;
use common\models\Setting;
use Yii;
use yii\db\Query;

final class SeoService
{
    private const INDEXABLE_ROUTES = [
        'site/index',
        'news/index',
        'news/view',
        'server/index',
        'shop/index',
        'vote/index',
        'page/view',
        'ratings/index',
    ];

    private const LOCALES = [
        'ru' => 'ru_RU',
        'en' => 'en_US',
        'de' => 'de_DE',
    ];

    public static function isIndexable(
        ?string $route = null
    ): bool {
        $route ??= Yii::$app->controller?->route ?? '';

        if(!in_array($route, self::INDEXABLE_ROUTES, true))
        {
            return false;
        }

        if(
            $route === 'ratings/index'
            && (string)Setting::value(
                'ratings.public_enabled',
                '0'
            ) !== '1'
        )
        {
            return false;
        }

        if(
            $route === 'shop/index'
            && (string)Setting::value(
                'shop.enabled',
                '1'
            ) !== '1'
        )
        {
            return false;
        }

        return true;
    }

    public static function description(
        ?string $explicit = null
    ): string {
        $explicit = trim((string)$explicit);

        if($explicit !== '')
        {
            return self::plainText($explicit, 320);
        }

        $lang = Yii::$app->language;
        $route = Yii::$app->controller?->route ?? '';

        $descriptions = [
            'ru' => [
                'site/index' => 'HiveTech — технологический Minecraft-сервер 1.12.2 с собственным лаунчером, модами, достижениями, статистикой игроков и единым аккаунтом.',
                'news/index' => 'Новости Minecraft-проекта HiveTech: обновления сервера, новые возможности, достижения, моды и изменения проекта.',
                'server/index' => 'Серверы HiveTech: статус, онлайн, версия Minecraft и список игровых модификаций.',
                'shop/index' => 'Магазин HiveTech: игровые возможности и привилегии проекта без pay-to-win механик.',
                'vote/index' => 'Голосование за HiveTech на Minecraft-мониторингах и награды за поддержку проекта.',
                'ratings/index' => 'Рейтинг игроков HiveTech по Achievement Score и полученным достижениям.',
            ],
            'en' => [
                'site/index' => 'HiveTech is a technology-focused Minecraft 1.12.2 server with its own launcher, modpack, achievements, player statistics and unified account.',
                'news/index' => 'HiveTech Minecraft project news: server updates, new features, achievements, mods and project changes.',
                'server/index' => 'HiveTech servers: status, online players, Minecraft version and installed gameplay mods.',
                'shop/index' => 'HiveTech store with project privileges and gameplay features built around a non-pay-to-win approach.',
                'vote/index' => 'Vote for HiveTech on Minecraft server lists and receive rewards for supporting the project.',
                'ratings/index' => 'HiveTech player ranking by Achievement Score and unlocked achievements.',
            ],
            'de' => [
                'site/index' => 'HiveTech ist ein technikorientierter Minecraft-1.12.2-Server mit eigenem Launcher, Modpack, Erfolgen, Spielerstatistiken und gemeinsamem Account.',
                'news/index' => 'Neuigkeiten zum Minecraft-Projekt HiveTech: Server-Updates, neue Funktionen, Erfolge, Mods und Projektänderungen.',
                'server/index' => 'HiveTech-Server: Status, Spieler online, Minecraft-Version und installierte Spielmods.',
                'shop/index' => 'HiveTech-Shop mit Projektprivilegien und Spielfunktionen ohne Pay-to-win-Ausrichtung.',
                'vote/index' => 'Stimme auf Minecraft-Serverlisten für HiveTech ab und erhalte Belohnungen für deine Unterstützung.',
                'ratings/index' => 'HiveTech-Spielerrangliste nach Achievement Score und freigeschalteten Erfolgen.',
            ],
        ];

        return $descriptions[$lang][$route]
            ?? $descriptions['en'][$route]
            ?? $descriptions[$lang]['site/index']
            ?? $descriptions['en']['site/index'];
    }

    public static function canonicalUrl(
        ?string $language = null
    ): string {
        $language ??= Yii::$app->language;

        $path = '/' . ltrim(
            (string)Yii::$app->request->pathInfo,
            '/'
        );

        if($path === '/')
        {
            $path = '/';
        }

        $query = [];

        if(
            Yii::$app->controller?->route === 'news/index'
            && (int)Yii::$app->request->get('page', 1) > 1
        )
        {
            $query['page'] =
                (int)Yii::$app->request->get('page');
        }

        return self::absolutePath(
            $path,
            $language,
            $query
        );
    }

    public static function alternateUrls(
        ?array $languages = null
    ): array {
        $languages ??= array_keys(
            LanguageBootstrap::LANGUAGES
        );

        $result = [];

        foreach($languages as $language)
        {
            if(
                !isset(
                    LanguageBootstrap::LANGUAGES[$language]
                )
            )
            {
                continue;
            }

            $result[$language] =
                self::canonicalUrl($language);
        }

        $defaultLanguage =
            isset($result['en'])
                ? 'en'
                : (array_key_first($result) ?: 'ru');

        if(isset($result[$defaultLanguage]))
        {
            $result['x-default'] =
                $result[$defaultLanguage];
        }

        return $result;
    }

    public static function absolutePath(
        string $path,
        string $language,
        array $query = []
    ): string {
        $base = rtrim(
            (string)Yii::$app->params['appUrl'],
            '/'
        );

        $path = '/' . ltrim($path, '/');

        $query = array_merge(
            $query,
            ['lang' => $language]
        );

        return $base
            . ($path === '/' ? '/' : $path)
            . '?'
            . http_build_query(
                $query,
                '',
                '&',
                PHP_QUERY_RFC3986
            );
    }

    public static function contentLanguages(
        string $type,
        int $id
    ): array {
        $table = match($type) {
            'news' => '{{%news_translations}}',
            'page' => '{{%page_translations}}',
            default => null,
        };

        if($table === null)
        {
            return array_keys(
                LanguageBootstrap::LANGUAGES
            );
        }

        $languages = ['ru'];

        $translated = (new Query())
            ->from($table)
            ->select('language')
            ->where([
                $type . '_id' => $id,
            ])
            ->column();

        foreach($translated as $language)
        {
            $language = (string)$language;

            if(
                isset(
                    LanguageBootstrap::LANGUAGES[$language]
                )
                && !in_array(
                    $language,
                    $languages,
                    true
                )
            )
            {
                $languages[] = $language;
            }
        }

        return $languages;
    }

    public static function locale(
        ?string $language = null
    ): string {
        $language ??= Yii::$app->language;

        return self::LOCALES[$language]
            ?? self::LOCALES['en'];
    }

    public static function logoUrl(): string
    {
        return rtrim(
            (string)Yii::$app->params['appUrl'],
            '/'
        ) . '/favicon-512.png';
    }

    public static function verificationMeta(): array
    {
        return array_filter([
            'google-site-verification' =>
                trim((string)ht_env(
                    'GOOGLE_SITE_VERIFICATION',
                    ''
                )),
            'yandex-verification' =>
                trim((string)ht_env(
                    'YANDEX_SITE_VERIFICATION',
                    ''
                )),
            'msvalidate.01' =>
                trim((string)ht_env(
                    'BING_SITE_VERIFICATION',
                    ''
                )),
        ]);
    }

    public static function websiteStructuredData(): array
    {
        $appName = (string)Yii::$app->params['appName'];
        $appUrl = rtrim(
            (string)Yii::$app->params['appUrl'],
            '/'
        );

        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $appName,
                'url' => $appUrl . '/',
                'inLanguage' => array_keys(
                    LanguageBootstrap::LANGUAGES
                ),
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $appName,
                'url' => $appUrl . '/',
                'logo' => self::logoUrl(),
            ],
        ];
    }

    public static function plainText(
        string $html,
        int $maxLength
    ): string {
        $value = html_entity_decode(
            strip_tags($html),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $value = trim((string)preg_replace(
            '/\s+/u',
            ' ',
            $value
        ));

        if(mb_strlen($value) <= $maxLength)
        {
            return $value;
        }

        return rtrim(
            mb_substr(
                $value,
                0,
                max(1, $maxLength - 1)
            )
        ) . '…';
    }
}
