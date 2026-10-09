<?php

use backend\assets\AppAsset;
use yii\helpers\{Html, Url};

AppAsset::register($this);

$controllerId = Yii::$app->controller->id;
$appUrl = rtrim((string)(Yii::$app->params['appUrl'] ?? ''), '/');

$icon = static function(string $name): string {
    $paths = [
        'dashboard' => '<path d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'moderation' => '<path d="M12 3 4 6v6c0 5 3.4 8 8 9 4.6-1 8-4 8-9V6l-8-3Z"/><path d="M9 12h6M12 9v6"/>',
        'rbac' => '<circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2"/><path d="M17 8h4M19 6v4M17 15h4"/>',
        'support' => '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/><path d="M8 9h8M8 13h5"/>',
        'news' => '<path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/>',
        'pages' => '<path d="M6 2h9l5 5v15H6z"/><path d="M14 2v6h6M9 13h6M9 17h6"/>',
        'slides' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="m7 16 4-4 3 3 3-3 2 2"/><circle cx="8" cy="9" r="1"/>',
        'shop' => '<path d="M3 5h18l-2 9H6L3 5Z"/><path d="M8 19h.01M17 19h.01"/>',
        'vote' => '<path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
        'privilege' => '<path d="m12 2 3 6 6 .9-4.5 4.4 1.1 6.2L12 16.6 6.4 19.5l1.1-6.2L3 8.9 9 8l3-6Z"/>',
        'promo' => '<path d="M20 12v8H4v-8M2 7h20v5H2zM12 7v13M12 7H7.5A2.5 2.5 0 1 1 10 4.5L12 7Zm0 0h4.5A2.5 2.5 0 1 0 14 4.5L12 7Z"/>',
        'payments' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/>',
        'delivery' => '<path d="M3 7h13v10H3z"/><path d="M16 10h3l2 3v4h-5z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
        'hwid' => '<rect x="5" y="5" width="14" height="14" rx="2"/><path d="M9 1v4M15 1v4M9 19v4M15 19v4M1 9h4M1 15h4M19 9h4M19 15h4"/><rect x="9" y="9" width="6" height="6"/>',
        'servers' => '<rect x="3" y="4" width="18" height="6" rx="2"/><rect x="3" y="14" width="18" height="6" rx="2"/><path d="M7 7h.01M7 17h.01"/>',
        'counter' => '<path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.1A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3v-4h.1A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3h4v.1A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.1.37.3.71.6 1 .3.28.68.42 1.1.4h.1v4h-.1a1.7 1.7 0 0 0-1.7.6Z"/>',
        'audit' => '<path d="M4 3h16v18H4z"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'external' => '<path d="M14 3h7v7M10 14 21 3"/><path d="M21 14v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h6"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close' => '<path d="M6 6l12 12M18 6 6 18"/>',
    ];

    $body = $paths[$name] ?? $paths['dashboard'];

    return '<svg class="admin-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$body.'</svg>';
};

$navGroups = [
    [
        'label' => 'Рабочее место',
        'items' => [
            ['dashboard/index', 'Обзор', 'adminPanel', 'dashboard', ['dashboard']],
            ['user/index', 'Пользователи', 'viewUsers', 'users', ['user']],
            ['moderation/index', 'Игровая модерация', 'viewModeration', 'moderation', ['moderation']],
            ['support/index', 'Поддержка', 'manageSupport', 'support', ['support']],
        ],
    ],
    [
        'label' => 'Контент',
        'items' => [
            ['news/index', 'Новости', 'manageContent', 'news', ['news', 'news-category']],
            ['page/index', 'Страницы', 'manageContent', 'pages', ['page']],
            ['slide/index', 'Слайдер', 'manageContent', 'slides', ['slide']],
        ],
    ],
    [
        'label' => 'Монетизация',
        'items' => [
            ['shop/index', 'Магазин', 'manageShop', 'shop', ['shop']],
            ['privilege/index', 'Привилегии', 'managePrivileges', 'privilege', ['privilege']],
            ['promo/index', 'Промокоды', 'manageShop', 'promo', ['promo']],
            ['payment/index', 'Платежи', 'managePayments', 'payments', ['payment']],
            ['voting/index', 'Голосования', 'manageShop', 'vote', ['voting']],
        ],
    ],
    [
        'label' => 'Система',
        'items' => [
            ['server/index', 'Серверы', 'manageServers', 'servers', ['server']],
            ['delivery/index', 'Очередь выдачи', 'manageDelivery', 'delivery', ['delivery']],
            ['hwid/index', 'HWID', 'manageHwid', 'hwid', ['hwid']],
            ['counter/index', 'Счётчики', 'manageSettings', 'counter', ['counter']],
            ['setting/index', 'Настройки', 'manageSettings', 'settings', ['setting']],
            ['rbac/index', 'Роли и права', 'manageRbac', 'rbac', ['rbac']],
            ['audit/index', 'Аудит', 'viewAudit', 'audit', ['audit']],
        ],
    ],
];

$sectionName = 'Администрирование';
foreach($navGroups as $group)
{
    foreach($group['items'] as $item)
    {
        if(in_array($controllerId, $item[4], true))
        {
            $sectionName = $group['label'];
            break 2;
        }
    }
}

$userRole = '';
if(!Yii::$app->user->isGuest)
{
    $roles = Yii::$app->authManager->getRolesByUser(
        (int)Yii::$app->user->id
    );
    $userRole = array_key_first($roles) ?: '';
}
?>
<?php $this->beginPage() ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode(
        $this->title
            ? $this->title . ' — HiveTech Admin'
            : 'HiveTech Admin'
    ) ?></title>
    <?php $this->head() ?>
</head>
<body class="<?= Yii::$app->user->isGuest ? 'admin-auth-page' : 'admin-body' ?>">
<?php $this->beginBody() ?>

<?php if(Yii::$app->user->isGuest): ?>

    <main class="admin-auth-shell">
        <?= $content ?>
    </main>

<?php else: ?>

    <div class="admin-shell">
        <aside class="admin-side" id="admin-sidebar">
            <div class="admin-side-head">
                <a class="admin-brand" href="<?= Url::to(['/dashboard/index']) ?>">
                    <span class="admin-brand-mark">H</span>
                    <span class="admin-brand-copy">
                        <strong>HiveTech</strong>
                        <small>CONTROL CENTER</small>
                    </span>
                </a>

                <button
                    type="button"
                    class="admin-sidebar-close"
                    data-admin-sidebar-close
                    aria-label="Закрыть меню"
                >
                    <?= $icon('close') ?>
                </button>
            </div>

            <nav class="admin-nav" aria-label="Административное меню">
                <?php foreach($navGroups as $group): ?>
                    <?php
                    $visibleItems = array_values(array_filter(
                        $group['items'],
                        static fn(array $item): bool =>
                            Yii::$app->user->can($item[2])
                    ));

                    if(!$visibleItems)
                    {
                        continue;
                    }
                    ?>

                    <div class="admin-nav-group">
                        <div class="admin-nav-label">
                            <?= Html::encode($group['label']) ?>
                        </div>

                        <?php foreach($visibleItems as $item): ?>
                            <?php
                            [$route, $label, $permission, $iconName, $controllers] = $item;
                            $active = in_array(
                                $controllerId,
                                $controllers,
                                true
                            );
                            ?>
                            <a
                                class="admin-nav-link<?= $active ? ' is-active' : '' ?>"
                                href="<?= Url::to(['/' . $route]) ?>"
                                <?= $active ? 'aria-current="page"' : '' ?>
                            >
                                <?= $icon($iconName) ?>
                                <span><?= Html::encode($label) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </nav>

            <div class="admin-side-footer">
                <?php if($appUrl !== ''): ?>
                    <a
                        class="admin-site-link"
                        href="<?= Html::encode($appUrl) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <?= $icon('external') ?>
                        <span>Открыть сайт</span>
                    </a>
                <?php endif; ?>

                <div class="admin-user-card">
                    <div class="admin-avatar">
                        <?= Html::encode(
                            mb_strtoupper(
                                mb_substr(
                                    (string)Yii::$app->user->identity->username,
                                    0,
                                    1
                                )
                            )
                        ) ?>
                    </div>
                    <div class="admin-user-copy">
                        <strong><?= Html::encode(
                            Yii::$app->user->identity->username
                        ) ?></strong>
                        <span><?= Html::encode(
                            $userRole !== '' ? $userRole : 'staff'
                        ) ?></span>
                    </div>
                </div>

                <?= Html::beginForm(
                    ['/site/logout'],
                    'post',
                    ['class' => 'admin-logout-form']
                ) ?>
                    <?= Html::submitButton(
                        $icon('logout') . '<span>Выйти</span>',
                        [
                            'class' => 'admin-logout-button',
                        ]
                    ) ?>
                <?= Html::endForm() ?>
            </div>
        </aside>

        <button
            class="admin-sidebar-backdrop"
            type="button"
            data-admin-sidebar-close
            aria-label="Закрыть меню"
        ></button>

        <main class="admin-main">
            <header class="admin-topbar">
                <div class="admin-page-heading">
                    <button
                        type="button"
                        class="admin-menu-toggle"
                        data-admin-sidebar-open
                        aria-controls="admin-sidebar"
                        aria-label="Открыть меню"
                    >
                        <?= $icon('menu') ?>
                    </button>

                    <div>
                        <div class="admin-kicker">
                            <?= Html::encode($sectionName) ?>
                        </div>
                        <h1><?= Html::encode(
                            $this->title ?: 'HiveTech Admin'
                        ) ?></h1>
                    </div>
                </div>

                <div class="admin-topbar-user">
                    <span><?= Html::encode(
                        Yii::$app->user->identity->username
                    ) ?></span>
                    <span class="admin-online-dot" title="Авторизован"></span>
                </div>
            </header>

            <div class="admin-content">
                <?php foreach(['success', 'error', 'warning', 'info'] as $type): ?>
                    <?php if(Yii::$app->session->hasFlash($type)): ?>
                        <div
                            class="alert admin-alert alert-<?= $type === 'error'
                                ? 'danger'
                                : $type
                            ?>"
                            role="alert"
                        >
                            <?= Html::encode(
                                Yii::$app->session->getFlash($type)
                            ) ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?= $content ?>
            </div>
        </main>
    </div>

    <?php
    $this->registerJs(<<<'JS'
(function () {
    const body = document.body;
    const openButton = document.querySelector('[data-admin-sidebar-open]');
    const closeButtons = document.querySelectorAll('[data-admin-sidebar-close]');
    const navLinks = document.querySelectorAll('.admin-nav-link');

    const openSidebar = function () {
        body.classList.add('admin-sidebar-open');
    };

    const closeSidebar = function () {
        body.classList.remove('admin-sidebar-open');
    };

    if(openButton)
    {
        openButton.addEventListener('click', openSidebar);
    }

    closeButtons.forEach(function (button) {
        button.addEventListener('click', closeSidebar);
    });

    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            if(window.innerWidth <= 980)
            {
                closeSidebar();
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if(event.key === 'Escape')
        {
            closeSidebar();
        }
    });
})();
JS);
    ?>

<?php endif; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
