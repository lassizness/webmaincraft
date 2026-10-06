<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require $root . '/common/bootstrap.php';
defined('YII_DEBUG') or define('YII_DEBUG', ht_bool('YII_DEBUG', false));
defined('YII_ENV') or define('YII_ENV', (string) ht_env('YII_ENV', 'prod'));
require $root . '/vendor/autoload.php';
require $root . '/vendor/yiisoft/yii2/Yii.php';
$config = yii\helpers\ArrayHelper::merge(
    require $root . '/common/config/main.php',
    require $root . '/backend/config/main.php'
);
(new yii\web\Application($config))->run();
