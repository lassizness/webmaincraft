<?php

use yii\helpers\{Html, Url};

$this->title = 'Обзор';

$metrics = [
    [
        'label' => 'Пользователи',
        'value' => $users,
        'note' => 'Все зарегистрированные аккаунты',
        'url' => ['/user/index'],
        'permission' => 'viewUsers',
        'class' => '',
    ],
    [
        'label' => 'Email не подтверждён',
        'value' => $unverifiedUsers,
        'note' => 'Не блокирует вход',
        'url' => ['/user/index', 'verified' => '0'],
        'permission' => 'viewUsers',
        'class' => $unverifiedUsers > 0 ? 'is-warning' : '',
    ],
    [
        'label' => 'Открытые тикеты',
        'value' => $openTickets,
        'note' => 'Требуют внимания поддержки',
        'url' => ['/support/index'],
        'permission' => 'manageSupport',
        'class' => $openTickets > 0 ? 'is-warning' : '',
    ],
    [
        'label' => 'Новые платежи',
        'value' => $newPayments,
        'note' => 'Ожидают обработки',
        'url' => ['/payment/index'],
        'permission' => 'managePayments',
        'class' => $newPayments > 0 ? 'is-danger' : '',
    ],
    [
        'label' => 'Покупки',
        'value' => $purchases,
        'note' => 'Всего покупок в магазине',
        'url' => ['/shop/index'],
        'permission' => 'manageShop',
        'class' => '',
    ],
];
?>

<div class="admin-kpi-grid mb-4">
    <?php foreach($metrics as $metric): ?>
        <?php
        $canOpen = Yii::$app->user->can($metric['permission']);
        $tag = $canOpen ? 'a' : 'div';
        $href = $canOpen
            ? ' href="' . Html::encode(Url::to($metric['url'])) . '"'
            : '';
        ?>
        <<?= $tag ?>
            class="admin-kpi <?= Html::encode($metric['class']) ?>"
            <?= $href ?>
        >
            <div class="admin-kpi-label">
                <?= Html::encode($metric['label']) ?>
            </div>
            <div class="admin-kpi-value">
                <?= Html::encode((string)$metric['value']) ?>
            </div>
            <div class="admin-kpi-note">
                <?= Html::encode($metric['note']) ?>
            </div>
        </<?= $tag ?>>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <section class="admin-panel h-100">
            <div class="admin-section-head">
                <div>
                    <h2 class="admin-panel-title">
                        Быстрые действия
                    </h2>
                    <div class="admin-panel-subtitle">
                        Частые операции без поиска по боковому меню.
                    </div>
                </div>
            </div>

            <div class="admin-quick-grid">
                <?php if(Yii::$app->user->can('viewUsers')): ?>
                    <a class="admin-quick-link" href="<?= Url::to(['/user/index']) ?>">
                        <span class="admin-quick-icon">⌕</span>
                        <span>Найти пользователя</span>
                    </a>
                <?php endif; ?>

                <?php if(Yii::$app->user->can('manageSupport')): ?>
                    <a class="admin-quick-link" href="<?= Url::to(['/support/index']) ?>">
                        <span class="admin-quick-icon">↗</span>
                        <span>Открыть поддержку</span>
                    </a>
                <?php endif; ?>

                <?php if(Yii::$app->user->can('manageContent')): ?>
                    <a class="admin-quick-link" href="<?= Url::to(['/news/create']) ?>">
                        <span class="admin-quick-icon">＋</span>
                        <span>Создать новость</span>
                    </a>
                <?php endif; ?>

                <?php if(Yii::$app->user->can('manageShop')): ?>
                    <a class="admin-quick-link" href="<?= Url::to(['/shop/product']) ?>">
                        <span class="admin-quick-icon">＋</span>
                        <span>Добавить товар</span>
                    </a>
                <?php endif; ?>

                <?php if(Yii::$app->user->can('manageServers')): ?>
                    <a class="admin-quick-link" href="<?= Url::to(['/server/edit']) ?>">
                        <span class="admin-quick-icon">＋</span>
                        <span>Добавить сервер</span>
                    </a>
                <?php endif; ?>

                <?php if(Yii::$app->user->can('manageSettings')): ?>
                    <a class="admin-quick-link" href="<?= Url::to(['/setting/index']) ?>">
                        <span class="admin-quick-icon">⚙</span>
                        <span>Настройки проекта</span>
                    </a>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="col-xl-4">
        <section class="admin-panel h-100">
            <h2 class="admin-panel-title mb-2">
                Контур аккаунта
            </h2>
            <p class="admin-panel-subtitle mb-3">
                Сайт и Gravit используют одну учётную запись.
            </p>

            <div class="d-grid gap-2 small">
                <div class="d-flex justify-content-between gap-3">
                    <span class="admin-muted">Пароли</span>
                    <span class="admin-value">bcrypt cost 10</span>
                </div>
                <div class="d-flex justify-content-between gap-3">
                    <span class="admin-muted">Email</span>
                    <span class="admin-value">не блокирует вход</span>
                </div>
                <div class="d-flex justify-content-between gap-3">
                    <span class="admin-muted">Блокировка</span>
                    <span class="admin-value text-end">сбрасывает web + launcher сессии</span>
                </div>
            </div>
        </section>
    </div>
</div>
