<?php

use common\models\User;
use common\components\LanguageBootstrap;
use frontend\components\Controller;

return [
    'id' => 'hivetech-frontend',
    'name' => ht_env('APP_NAME', 'HiveTech'),
    'basePath' => dirname(__DIR__),
    'bootstrap' => [LanguageBootstrap::class, 'log'],
    'controllerNamespace' => 'frontend\\controllers',
    'defaultRoute' => 'site/index',
    'components' => [
        'i18n' => [
            'translations' => [
                'ui*' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'ru',
                ],
                'app*' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'ru',
                    'fileMap' => [
                        'app' => 'app.php',
                    ],
                ],
            ],
        ],
        'request' => [
            'csrfParam' => '_csrf-hivetech',
            'cookieValidationKey' => ht_env('COOKIE_VALIDATION_KEY', 'CHANGE-ME'),
        ],
        'user' => [
            'identityClass' => User::class,
            'enableAutoLogin' => true,
            'loginUrl' => ['/auth/login'],
            'identityCookie' => [
                'name' => '_identity-hivetech',
                'httpOnly' => true,
                'secure' => ht_bool('COOKIE_SECURE', !ht_bool('YII_DEBUG', false)),
                'sameSite' => yii\web\Cookie::SAME_SITE_LAX,
                'domain' => (string) ht_env('COOKIE_DOMAIN', ''),
            ],
        ],
        'session' => [
            'name' => 'HIVETECHSESSID',
            'cookieParams' => [
                'lifetime' => 0,
                'path' => '/',
                'httponly' => true,
                'secure' => ht_bool('COOKIE_SECURE', !ht_bool('YII_DEBUG', false)),
                'samesite' => yii\web\Cookie::SAME_SITE_LAX,
                'domain' => (string) ht_env('COOKIE_DOMAIN', ''),
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'robots.txt' => 'seo/robots',
                'sitemap.xml' => 'seo/sitemap',
                'language/<lang:ru|en|de>' => 'language/switch',
                '' => 'site/index',
                'login' => 'auth/login',
                'register' => 'auth/register',
                'logout' => 'auth/logout',
                'verify-email' => 'auth/verify-email',
                'resend-verification' => 'auth/resend-verification',
                'forgot-password' => 'auth/request-password-reset',
                'reset-password' => 'auth/reset-password',
                'account' => 'account/index',
                'account/settings' => 'account/settings',
                'account/security' => 'account/security',
                'account/sessions' => 'account/sessions',
                'account/achievements' => 'account/achievements',
                'ratings' => 'ratings/index',
                'players/<username:[A-Za-z0-9_]{3,16}>' => 'ratings/player',
                'skins' => 'skin/index',
                'texture/skin/<username:[A-Za-z0-9_]{3,16}>.png' => 'texture/skin',
                'texture/cloak/<username:[A-Za-z0-9_]{3,16}>.png' => 'texture/cloak',
                'news' => 'news/index',
                'news/<slug:[a-z0-9-]+>' => 'news/view',
                'support' => 'support/index',
                'support/new' => 'support/create',
                'support/<id:\\d+>' => 'support/view',
                'support/attachment/<id:\\d+>' => 'support/attachment',
                'shop' => 'shop/index',
                'wallet' => 'wallet/index',
                'vote' => 'vote/index',
                'vote/<service:[a-z0-9_-]+>/callback' => 'vote/callback',
                'vote/<service:[a-z0-9_-]+>/<username:[A-Za-z0-9_]{3,16}>' => 'vote/go',
                'servers' => 'server/index',

                'GET internal/game/moderation/snapshot' =>
                    'internal-game/moderation-snapshot',
                'GET internal/game/moderation/changes' =>
                    'internal-game/moderation-changes',
                'GET internal/game/moderation/player/<uuid:[0-9A-Fa-f\\-]{36}>' =>
                    'internal-game/moderation-player',
                'POST internal/game/bridge/heartbeat' =>
                    'internal-game/bridge-heartbeat',

                'POST internal/game/telemetry/events' =>
                    'internal-game/telemetry-events',
                'POST internal/game/telemetry/heartbeat' =>
                    'internal-game/telemetry-heartbeat',

                'forum/sso' => 'forum/sso',
                'page/<slug:[a-z0-9-]+>' => 'page/view',
            ],
        ],
        'assetManager' => [
            'appendTimestamp' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => ht_bool('YII_DEBUG', false) ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                ],
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['info', 'warning', 'error'],
                    'categories' => ['recaptcha'],
                    'logFile' => '@runtime/logs/recaptcha.log',
                    'logVars' => [],
                ],
            ],
        ],
    ],
    'params' => require dirname(__DIR__, 2) . '/common/config/params.php',
];
