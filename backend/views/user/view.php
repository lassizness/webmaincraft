<?php

use common\models\User;
use yii\helpers\Html;

$this->title = 'Пользователь ' . $model->username;

$currentRole = array_key_first($roles) ?: 'user';

$statusLabels = [
    User::STATUS_ACTIVE => 'Активен',
    User::STATUS_PENDING => 'Pending',
    User::STATUS_BLOCKED => 'Заблокирован',
];

$statusClasses = [
    User::STATUS_ACTIVE => 'bg-success',
    User::STATUS_PENDING => 'bg-warning',
    User::STATUS_BLOCKED => 'bg-danger',
];

$roleLabels = [
    'user' => 'Игрок',
    'moderator' => 'Модератор',
    'admin' => 'Администратор',
];
?>

<div class="admin-section-head">
    <div>
        <?= Html::a(
            '← К списку пользователей',
            ['/user/index'],
            ['class' => 'small text-decoration-none']
        ) ?>
        <div class="d-flex gap-2 align-items-center mt-2 flex-wrap">
            <span class="badge <?= $statusClasses[$model->status] ?? 'bg-secondary' ?>">
                <?= Html::encode(
                    $statusLabels[$model->status] ?? $model->status
                ) ?>
            </span>
            <span class="badge <?= $model->email_verified
                ? 'bg-success'
                : 'bg-secondary'
            ?>">
                <?= $model->email_verified
                    ? 'Email подтверждён'
                    : 'Email не подтверждён'
                ?>
            </span>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-7">
        <section class="admin-panel h-100">
            <div class="admin-section-head">
                <div>
                    <h2 class="admin-panel-title">
                        Учётная запись
                    </h2>
                    <div class="admin-panel-subtitle">
                        Основные данные сайта и лаунчера.
                    </div>
                </div>
            </div>

            <div class="admin-detail-grid">
                <div class="admin-detail-item">
                    <div class="admin-detail-label">ID</div>
                    <div class="admin-detail-value">
                        <?= (int)$model->id ?>
                    </div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">Логин</div>
                    <div class="admin-detail-value fw-bold">
                        <?= Html::encode($model->username) ?>
                    </div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">UUID</div>
                    <div class="admin-detail-value font-monospace">
                        <?= Html::encode($model->uuid) ?>
                    </div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">Email</div>
                    <div class="admin-detail-value">
                        <?= Html::encode($model->email) ?>
                    </div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">Launcher accessToken</div>
                    <div class="admin-detail-value">
                        <?= $model->accessToken ? 'Установлен' : 'Нет' ?>
                    </div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">serverID</div>
                    <div class="admin-detail-value font-monospace">
                        <?= Html::encode($model->serverID ?: '—') ?>
                    </div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">Последний вход</div>
                    <div class="admin-detail-value">
                        <?= Html::encode($model->last_login_at ?: '—') ?>
                    </div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">Язык</div>
                    <div class="admin-detail-value text-uppercase">
                        <?= Html::encode((string)$model->language) ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-xl-5">
        <section class="admin-panel mb-3">
            <h2 class="admin-panel-title mb-1">
                Статус аккаунта
            </h2>
            <div class="admin-panel-subtitle mb-3">
                Блокировка сразу сбрасывает web- и launcher-сессии.
            </div>

            <div class="admin-status-row">
                <?php foreach([
                    User::STATUS_ACTIVE => 'Активировать',
                    User::STATUS_PENDING => 'Pending',
                    User::STATUS_BLOCKED => 'Заблокировать',
                ] as $status => $label): ?>
                    <?= Html::beginForm(
                        [
                            '/user/status',
                            'id' => $model->id,
                            'status' => $status,
                        ],
                        'post',
                        ['class' => 'd-inline']
                    ) ?>
                        <?= Html::submitButton(
                            $model->status === $status
                                ? ($statusLabels[$status] ?? $label)
                                : $label,
                            [
                                'class' => 'btn btn-sm '
                                    .($model->status === $status
                                        ? 'admin-status-button is-current'
                                        : ($status === User::STATUS_BLOCKED
                                            ? 'btn-outline-danger'
                                            : 'btn-outline-light'
                                        )
                                    ),
                                'disabled' => $model->status === $status,
                                'data-confirm' => $status === User::STATUS_BLOCKED
                                    ? 'Заблокировать пользователя и сбросить его активные сессии?'
                                    : null,
                            ]
                        ) ?>
                    <?= Html::endForm() ?>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="admin-panel">
            <h2 class="admin-panel-title mb-1">
                Роль в админке
            </h2>
            <div class="admin-panel-subtitle mb-3">
                Текущая роль: <?= Html::encode(
                    $roleLabels[$currentRole] ?? $currentRole
                ) ?>.
            </div>

            <div class="admin-status-row">
                <?php foreach($roleLabels as $role => $label): ?>
                    <?= Html::beginForm(
                        [
                            '/user/role',
                            'id' => $model->id,
                            'role' => $role,
                        ],
                        'post',
                        ['class' => 'd-inline']
                    ) ?>
                        <?= Html::submitButton(
                            $label,
                            [
                                'class' => 'btn btn-sm '
                                    .($currentRole === $role
                                        ? 'admin-status-button is-current'
                                        : 'btn-outline-primary'
                                    ),
                                'disabled' => $currentRole === $role,
                                'data-confirm' => $currentRole !== $role
                                    ? 'Изменить роль пользователя на «'
                                        .$label
                                        .'»?'
                                    : null,
                            ]
                        ) ?>
                    <?= Html::endForm() ?>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>

<section class="admin-panel mt-3">
    <div class="admin-section-head">
        <div>
            <h2 class="admin-panel-title">
                Gravit permissions
            </h2>
            <div class="admin-panel-subtitle">
                Точечные разрешения LaunchServer для этого UUID.
            </div>
        </div>
    </div>

    <?php if($launcherPermissions): ?>
        <div class="d-flex gap-2 flex-wrap mb-3">
            <?php foreach($launcherPermissions as $permission): ?>
                <span class="badge text-bg-secondary d-inline-flex gap-2 align-items-center">
                    <span class="font-monospace">
                        <?= Html::encode($permission->name) ?>
                    </span>

                    <?= Html::beginForm(
                        [
                            '/user/delete-launcher-permission',
                            'id' => $model->id,
                            'name' => $permission->name,
                        ],
                        'post',
                        ['class' => 'd-inline']
                    ) ?>
                        <?= Html::submitButton(
                            '×',
                            [
                                'class' => 'btn btn-link btn-sm text-white p-0 border-0',
                                'title' => 'Удалить permission',
                                'data-confirm' => 'Удалить это разрешение?',
                            ]
                        ) ?>
                    <?= Html::endForm() ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="admin-muted small mb-3">
            Дополнительных launcher permissions нет.
        </div>
    <?php endif; ?>

    <?= Html::beginForm(
        ['/user/add-launcher-permission', 'id' => $model->id],
        'post',
        ['class' => 'd-flex gap-2 flex-wrap']
    ) ?>
        <input
            class="form-control flex-grow-1"
            style="min-width:260px"
            name="permission"
            maxlength="100"
            placeholder="launchserver.profile.<uuid>.*"
            autocomplete="off"
        >
        <button class="btn btn-outline-primary" type="submit">
            Добавить
        </button>
    <?= Html::endForm() ?>
</section>
