<?php

use yii\helpers\Url;

?>

<aside class="account-nav">
    <a href="<?= Url::to(['/account/index']) ?>">
        <?= Yii::t('ui', 'Обзор') ?>
    </a>

    <a href="<?= Url::to(['/account/settings']) ?>">
        <?= Yii::t('ui', 'Профиль') ?>
    </a>

    <a href="<?= Url::to(['/account/security']) ?>">
        <?= Yii::t('ui', 'Безопасность') ?>
    </a>

    <a href="<?= Url::to(['/account/sessions']) ?>">
        <?= Yii::t('ui', 'Сессии') ?>
    </a>

    <a href="<?= Url::to(['/skin/index']) ?>">
        <?= Yii::t('ui', 'Персонализация персонажа') ?>
    </a>

    <a href="<?= Url::to(['/wallet/index']) ?>">
        <?= Yii::t('ui', 'Баланс') ?>
    </a>

    <a href="<?= Url::to(['/privilege/index']) ?>">
        <?= Yii::t('ui', 'Статус') ?>
    </a>

    <a href="<?= Url::to(['/shop/promo']) ?>">
        <?= Yii::t('ui', 'Промокод') ?>
    </a>

    <a href="<?= Url::to(['/vote/index']) ?>">
        <?= Yii::t('ui', 'Голосование') ?>
    </a>

    <a href="<?= Url::to(['/support/index']) ?>">
        <?= Yii::t('ui', 'Поддержка') ?>
    </a>

    <?php if(Yii::$app->user->can('admin')): ?>
        <a href="/admin/">
            <?= Yii::t('ui', 'Админ-панель') ?>
        </a>
    <?php endif; ?>
</aside>
