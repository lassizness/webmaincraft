<?php

use common\models\User;

return [
    'id' => 'hivetech-backend',
    'name' => 'HiveTech Admin',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'backend\\controllers',
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
            'csrfParam' => '_csrf-hivetech-admin',
            'cookieValidationKey' => ht_env('COOKIE_VALIDATION_KEY', 'CHANGE-ME') . '-admin',
        ],
        'user' => [
            'identityClass' => User::class,
            'enableAutoLogin' => true,
            'loginUrl' => ['site/login'],
            'identityCookie' => [
                'name' => '_identity-hivetech-admin',
                'httpOnly' => true,
                'secure' => ht_bool('COOKIE_SECURE', !ht_bool('YII_DEBUG', false)),
                'sameSite' => yii\web\Cookie::SAME_SITE_LAX,
                'domain' => (string) ht_env('COOKIE_DOMAIN', ''),
            ],
        ],
        'session' => [
            'name' => 'HIVETECHADMINSESSID',
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
