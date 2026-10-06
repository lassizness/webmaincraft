<?php

declare(strict_types=1);

namespace common\components;

use yii\base\BootstrapInterface;

final class LanguageBootstrap implements BootstrapInterface
{
    public const COOKIE = 'hivegrid_language';

    public const LANGUAGES = [
        'ru' => 'Русский',
        'en' => 'English',
        'de' => 'Deutsch',
    ];

    public function bootstrap($app): void
    {
        if(PHP_SAPI === 'cli')
        {
            return;
        }

        /*
         * 1. Ручной выбор пользователя всегда имеет приоритет.
         */
        $cookieLanguage = trim((string)$app->request->cookies->getValue(
            self::COOKIE,
            ''
        ));

        if(isset(self::LANGUAGES[$cookieLanguage]))
        {
            $app->language = $cookieLanguage;
            $app->sourceLanguage = 'ru';
            return;
        }

        /*
         * 2. Для авторизованного пользователя используем сохранённый язык,
         * если cookie ещё нет (например, на новом устройстве).
         */
        if(!$app->user->isGuest)
        {
            $userLanguage = trim((string)($app->user->identity?->language ?? ''));

            if(isset(self::LANGUAGES[$userLanguage]))
            {
                $app->language = $userLanguage;
                $app->sourceLanguage = 'ru';
                return;
            }
        }

        /*
         * 3. Если позже внешний reverse proxy начнёт передавать страну,
         * используем её.
         *
         * Поддерживаем:
         * X-Country-Code: RU
         * CF-IPCountry: RU
         */
        $country = strtoupper(trim((string)(
            $app->request->headers->get('X-Country-Code')
            ?: $app->request->headers->get('CF-IPCountry')
            ?: ''
        )));

        if($country === 'RU')
        {
            $language = 'ru';
        }
        elseif(in_array($country, ['DE', 'AT', 'CH', 'LI'], true))
        {
            $language = 'de';
        }
        elseif($country !== '' && $country !== 'XX')
        {
            $language = 'en';
        }
        else
        {
            /*
             * 4. Пока GeoIP-заголовка нет — используем предпочтение браузера.
             *
             * en стоит первым специально:
             * неизвестный язык => English.
             */
            $language = $app->request->getPreferredLanguage([
                'en',
                'ru',
                'de',
            ]);

            if(!isset(self::LANGUAGES[$language]))
            {
                $language = 'en';
            }
        }

        $app->language = $language;
        $app->sourceLanguage = 'ru';
    }
}
