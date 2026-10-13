<?php

use common\models\User;

return [
    'id' => 'hivetech-backend',
    'name' => 'HiveTech Admin',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'backend\\controllers',
    'defaultRoute' => 'dashboard/index',
    'components' => [
        'i18n' => [
            'translations' => [
                'ui*' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'ru',
                ],
                'achievements*' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'ru',
                    'fileMap' => [
                        'achievements' => 'achievements.php',
                    ],
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
            'baseUrl' => '/admin',
            'csrfParam' => '_csrf-hivetech-admin',
            'cookieValidationKey' => ht_env(
                'COOKIE_VALIDATION_KEY',
                'CHANGE-ME'
            ),
        ],
        'user' => [
            'identityClass' => User::class,
            'enableAutoLogin' => true,
            'loginUrl' => ['site/login'],
            'identityCookie' => [
                'name' => '_identity-hivetech',
                'httpOnly' => true,
                'secure' => ht_bool(
                    'COOKIE_SECURE',
                    !ht_bool('YII_DEBUG', false)
                ),
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
                'secure' => ht_bool(
                    'COOKIE_SECURE',
                    !ht_bool('YII_DEBUG', false)
                ),
                'samesite' => yii\web\Cookie::SAME_SITE_LAX,
                'domain' => (string) ht_env('COOKIE_DOMAIN', ''),
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'errorHandler' => ['errorAction' => 'site/error'],
        'log' => [
            'traceLevel' => ht_bool('YII_DEBUG', false) ? 3 : 0,
            'targets' => [[
                'class' => yii\log\FileTarget::class,
                'levels' => ['error', 'warning'],
                'logVars' => [],
            ]],
        ],
    ],
    'params' => require dirname(__DIR__, 2) . '/common/config/params.php',
];
