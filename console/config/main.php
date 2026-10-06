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
];
