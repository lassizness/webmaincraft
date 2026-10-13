<?php

use common\models\Setting;
use yii\helpers\{Html, Url};

$route = Yii::$app->controller?->route ?? '';

$items = [
    ['account/index', ['/account/index'], Yii::t('ui', 'Обзор')],
    ['account/settings', ['/account/settings'], Yii::t('ui', 'Профиль')],
    ['account/security', ['/account/security'], Yii::t('ui', 'Безопасность')],
    ['account/sessions', ['/account/sessions'], Yii::t('ui', 'Сессии')],
    ['skin/index', ['/skin/index'], Yii::t('ui', 'Персонализация персонажа')],
    ['wallet/index', ['/wallet/index'], Yii::t('ui', 'Баланс')],
    ['privilege/index', ['/privilege/index'], Yii::t('ui', 'Статус')],
    ['shop/promo', ['/shop/promo'], Yii::t('ui', 'Промокод')],
    ['vote/index', ['/vote/index'], Yii::t('ui', 'Голосование')],
];

if((string)Setting::value('achievements.enabled', '1') === '1')
{
    $items[] = [
        'account/achievements',
        ['/account/achievements'],
        Yii::t('ui', 'Достижения'),
    ];
}

$items[] = [
    'support/index',
    ['/support/index'],
    Yii::t('ui', 'Поддержка'),
];

?>

<aside class="account-nav" aria-label="<?= Html::encode(Yii::t('ui', 'Навигация личного кабинета')) ?>">
    <?php foreach($items as [$itemRoute, $url, $label]): ?>
        <?php
        $active = $route === $itemRoute
            || (
                $itemRoute === 'support/index'
                && str_starts_with($route, 'support/')
            );
        ?>
        <a
            href="<?= Url::to($url) ?>"
            class="<?= $active ? 'is-active' : '' ?>"
            <?= $active ? 'aria-current="page"' : '' ?>
        >
            <?= Html::encode($label) ?>
        </a>
    <?php endforeach; ?>

    <?php if(Yii::$app->user->can('admin')): ?>
        <a href="/admin/">
            <?= Yii::t('ui', 'Админ-панель') ?>
        </a>
    <?php endif; ?>
</aside>
