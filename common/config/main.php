<?php

use common\models\User;

$dbUser = ht_env('HIVETECH_WEB_DB_USER', 'hivetech_web');
$dbPassword = ht_env('HIVETECH_WEB_DB_PASSWORD', '');
if (PHP_SAPI === 'cli' && ht_bool('HIVETECH_USE_MIGRATION_DB', false) && ht_env('HIVETECH_MIGRATION_DB_USER')) {
    $dbUser = ht_env('HIVETECH_MIGRATION_DB_USER');
    $dbPassword = ht_env('HIVETECH_MIGRATION_DB_PASSWORD', '');
}

return [
    'aliases' => [
        '@common' => dirname(__DIR__),
        '@frontend' => dirname(dirname(__DIR__)) . '/frontend',
        '@backend' => dirname(dirname(__DIR__)) . '/backend',
        '@console' => dirname(dirname(__DIR__)) . '/console',
        '@storage' => dirname(dirname(__DIR__)) . '/storage',
        '@bower' => dirname(dirname(__DIR__)) . '/vendor/bower-asset',
        '@npm' => dirname(dirname(__DIR__)) . '/vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'db' => [
            'class' => yii\db\Connection::class,
            'dsn' => sprintf(
                'mysql:host=%s;port=%s;dbname=%s',
                ht_env('HIVETECH_DB_HOST', '127.0.0.1'),
                ht_env('HIVETECH_DB_PORT', '3306'),
                ht_env('HIVETECH_DB_NAME', 'hivetech_core')
            ),
            'username' => $dbUser,
            'password' => $dbPassword,
            'charset' => 'utf8mb4',
            'attributes' => [PDO::ATTR_EMULATE_PREPARES => false],
            'enableSchemaCache' => !ht_bool('YII_DEBUG', false),
            'schemaCacheDuration' => 3600,
            'schemaCache' => 'cache',
        ],
        'cache' => [
            'class' => yii\caching\FileCache::class,
        ],
        'mailer' => array_filter([
            'class' => yii\symfonymailer\Mailer::class,
            'viewPath' => '@common/mail',
            'useFileTransport' => !ht_env('SMTP_HOST'),
            'transport' => ht_env('SMTP_HOST') ? [
                'scheme' => strtolower((string) ht_env('SMTP_ENCRYPTION', 'tls')) === 'ssl' ? 'smtps' : 'smtp',
                'host' => ht_env('SMTP_HOST'),
                'username' => ht_env('SMTP_USER', ''),
                'password' => ht_env('SMTP_PASSWORD', ''),
                'port' => (int) ht_env('SMTP_PORT', 587),
            ] : null,
            'messageConfig' => [
                'from' => [
                    ht_env('MAIL_FROM', 'no-reply@localhost') => ht_env('MAIL_FROM_NAME', 'HiveTech'),
                ],
            ],
        ], static fn($v) => $v !== null),
        'authManager' => [
            'class' => yii\rbac\DbManager::class,
        ],
    ],
    'params' => require __DIR__ . '/params.php',
];
