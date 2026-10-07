<?php

return [
    'id' => 'hivetech-console',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'console\\controllers',
    'controllerMap' => [
        'migrate' => [
            'class' => yii\console\controllers\MigrateController::class,
            'migrationPath' => '@console/migrations',
        ],
    ],
    'components' => [
        'i18n' => [
            'translations' => [
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
    ],
];
