<?php

use common\models\{
    ModerationAction,
    RbacRoleMeta,
    User
};
use yii\helpers\Html;

$this->title = 'Пользователь ' . $model->username;

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

$currentRoleMeta = RbacRoleMeta::findOne([
    'role_name' => $currentRole,
]);

$currentRoleLabel = $currentRole === 'user'
    ? 'Игрок'
    : ($currentRoleMeta?->label ?: $currentRole);

$moderationTypeLabels = [
    ModerationAction::TYPE_WARN => 'Warn',
    ModerationAction::TYPE_MUTE => 'Mute',
    ModerationAction::TYPE_BAN => 'Ban',
];

$moderationStatusLabels = [
    ModerationAction::STATUS_ACTIVE => 'Активно',
    ModerationAction::STATUS_RECORDED => 'Записано',
    ModerationAction::STATUS_EXPIRED => 'Истекло',
    ModerationAction::STATUS_REVOKED => 'Снято',
];

$operatorStatusLabels = [
    'pending' => 'В очереди',
    'processing' => 'Выполняется',
    'done' => 'Выполнено',
    'failed' => 'Ошибка',
];

$operatorStatusClasses = [
    'pending' => 'bg-secondary',
    'processing' => 'bg-info',
    'done' => 'bg-success',
    'failed' => 'bg-danger',
];

$serverOptions = [];
foreach($minecraftServers as $server)
{
    $serverOptions[(int)$server->id] =
        $server->name . ' (' . $server->code . ')';
}
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
            <?php if($activeModerationCount > 0): ?>
                <span class="badge bg-danger">
                    Активных наказаний: <?= (int)$activeModerationCount ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if(
        $canManageTarget
        && Yii::$app->user->can('viewModeration')
        && (
            Yii::$app->user->can('createWarn')
            || Yii::$app->user->can('createMute')
            || Yii::$app->user->can('createBan')
        )
    ): ?>
        <div class="admin-actions">
            <?= Html::a(
                'Наказать игрока',
                ['/moderation/create', 'userId' => $model->id],
                ['class' => 'btn btn-outline-danger']
            ) ?>
        </div>
    <?php endif; ?>
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
                Это системный статус аккаунта, а не игровой бан.
            </div>

            <?php if(
                $canManageTarget
                && Yii::$app->user->can('manageUserStatus')
            ): ?>
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
                                        ? 'Полностью заблокировать аккаунт и сбросить web/launcher-сессии?'
                                        : null,
                                ]
                            ) ?>
                        <?= Html::endForm() ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <span class="badge <?= $statusClasses[$model->status] ?? 'bg-secondary' ?>">
                    <?= Html::encode(
                        $statusLabels[$model->status] ?? $model->status
                    ) ?>
                </span>
            <?php endif; ?>
        </section>

        <section class="admin-panel">
            <h2 class="admin-panel-title mb-1">
                Роль в админке
            </h2>
            <div class="admin-panel-subtitle mb-3">
                Текущая роль:
                <strong><?= Html::encode($currentRoleLabel) ?></strong>
                <code><?= Html::encode($currentRole) ?></code>
            </div>

            <?php if(
                $canManageTarget
                && Yii::$app->user->can('manageUserRoles')
                && $assignableRoles
            ): ?>
                <div class="d-grid gap-2">
                    <?php foreach($assignableRoles as $roleName => $roleData): ?>
                        <?= Html::beginForm(
                            [
                                '/user/role',
                                'id' => $model->id,
                                'role' => $roleName,
                            ],
                            'post'
                        ) ?>
                            <?= Html::submitButton(
                                $roleData['label']
                                    . ' · level '
                                    . $roleData['level'],
                                [
                                    'class' => 'btn btn-sm w-100 text-start '
                                        .($currentRole === $roleName
                                            ? 'admin-status-button is-current'
                                            : 'btn-outline-primary'
                                        ),
                                    'disabled' => $currentRole === $roleName,
                                    'data-confirm' => $currentRole !== $roleName
                                        ? 'Назначить роль «'
                                            .$roleData['label']
                                            .'»?'
                                        : null,
                                ]
                            ) ?>
                        <?= Html::endForm() ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php if(Yii::$app->user->can('viewTelemetry')): ?>
    <?php
    $telemetryStatLabels = [
        'playtime_seconds' => 'Игровое время',
        'blocks.break.total' => 'Блоков сломано',
        'blocks.place.total' => 'Блоков установлено',
        'entity.kill.total' => 'Мобов убито',
        'player.death.total' => 'Смертей',
        'item.craft.total' => 'Предметов создано',
        'item.smelt.total' => 'Переплавлено',
        'dimensions.visited' => 'Измерений',
    ];

    $formatTelemetryStat = static function(
        string $key,
        int $value
    ): string {
        if($key === 'playtime_seconds')
        {
            return intdiv($value, 3600)
                . ' ч '
                . intdiv($value % 3600, 60)
                . ' мин';
        }

        return number_format($value, 0, ',', ' ');
    };
    ?>

    <section class="admin-panel mt-3">
        <div class="admin-section-head">
            <div>
                <h2 class="admin-panel-title">
                    Игровая статистика
                </h2>
                <div class="admin-panel-subtitle">
                    Данные HiveTechTelemetry, достижения и последние события игрока.
                </div>
            </div>

            <div class="admin-actions">
                <?php if($activeGameSession): ?>
                    <span class="badge bg-success">
                        Online · <?= Html::encode(
                            $activeGameSession->server?->name ?: 'сервер'
                        ) ?>
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary">
                        Offline
                    </span>
                <?php endif; ?>

                <?= Html::a(
                    'Все события',
                    [
                        '/telemetry/events',
                        'q' => $model->username,
                    ],
                    ['class' => 'btn btn-sm btn-outline-primary']
                ) ?>
            </div>
        </div>

        <div class="admin-kpi-grid mb-3">
            <div class="admin-kpi">
                <div class="admin-kpi-label">
                    Achievement score
                </div>
                <div class="admin-kpi-value">
                    <?= number_format(
                        (int)($achievementSummary['score'] ?? 0),
                        0,
                        ',',
                        ' '
                    ) ?>
                </div>
                <div class="admin-kpi-note">
                    очков достижений
                </div>
            </div>

            <div class="admin-kpi">
                <div class="admin-kpi-label">
                    Достижения
                </div>
                <div class="admin-kpi-value">
                    <?= (int)($achievementSummary['unlocked'] ?? 0) ?>
                    /
                    <?= (int)($achievementSummary['total'] ?? 0) ?>
                </div>
                <div class="admin-kpi-note">
                    разблокировано
                </div>
            </div>

            <div class="admin-kpi">
                <div class="admin-kpi-label">
                    Последнее достижение
                </div>
                <div
                    class="admin-kpi-value"
                    style="font-size:18px"
                >
                    <?= Html::encode(
                        (string)($achievementSummary['last_unlock'] ?? '—')
                    ) ?>
                </div>
            </div>
        </div>

        <?php if(!$playerStats): ?>
            <div class="admin-muted mb-3">
                Игровая статистика пока не поступала.
            </div>
        <?php else: ?>
            <div class="admin-detail-grid mb-3">
                <?php
                $renderedStats = 0;
                foreach($telemetryStatLabels as $key => $label):
                    if(!array_key_exists($key, $playerStats))
                    {
                        continue;
                    }

                    $renderedStats++;
                ?>
                    <div class="admin-detail-item">
                        <div class="admin-detail-label">
                            <?= Html::encode($label) ?>
                        </div>
                        <div class="admin-detail-value fw-bold">
                            <?= Html::encode(
                                $formatTelemetryStat(
                                    $key,
                                    (int)$playerStats[$key]
                                )
                            ) ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if($renderedStats === 0): ?>
                    <?php foreach(array_slice($playerStats, 0, 8, true) as $key => $value): ?>
                        <div class="admin-detail-item">
                            <div class="admin-detail-label font-monospace">
                                <?= Html::encode((string)$key) ?>
                            </div>
                            <div class="admin-detail-value fw-bold">
                                <?= number_format((int)$value, 0, ',', ' ') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <h3 class="admin-panel-title mt-4 mb-2">
            Последние события
        </h3>

        <?php if(!$recentTelemetry): ?>
            <div class="admin-muted">
                Событий пока нет.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Время</th>
                        <th>Событие</th>
                        <th>Object</th>
                        <th>Источник</th>
                        <th class="text-end">Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($recentTelemetry as $event): ?>
                        <tr>
                            <td>
                                <?= Html::encode(
                                    (string)$event->occurred_at
                                ) ?>
                            </td>
                            <td>
                                <code><?= Html::encode(
                                    (string)$event->event_type
                                ) ?></code>
                            </td>
                            <td>
                                <?= $event->object_id
                                    ? Html::tag(
                                        'code',
                                        Html::encode(
                                            (string)$event->object_id
                                        )
                                    )
                                    : '—'
                                ?>
                            </td>
                            <td>
                                <?= Html::encode(
                                    (string)$event->source
                                ) ?>
                            </td>
                            <td class="text-end">
                                <?= (int)$event->amount ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php if(Yii::$app->user->can('manageMinecraftOperators')): ?>
    <section class="admin-panel mt-3">
        <div class="admin-section-head">
            <div>
                <h2 class="admin-panel-title">
                    Minecraft OP
                </h2>
                <div class="admin-panel-subtitle">
                    Выдача и снятие операторских прав через RCON.
                    Роль администратора сайта и Minecraft OP не связаны автоматически.
                </div>
            </div>
        </div>

        <?php if(!$minecraftServers): ?>
            <div class="alert alert-warning mb-0">
                Нет включённых Minecraft-серверов.
            </div>
        <?php else: ?>
            <?= Html::beginForm(
                [
                    '/user/minecraft-operator',
                    'id' => $model->id,
                ],
                'post',
                [
                    'class' =>
                        'd-flex gap-2 flex-wrap align-items-end mb-3',
                ]
            ) ?>
                <?= Html::hiddenInput(
                    'operation',
                    '',
                    ['id' => 'minecraft-operator-operation']
                ) ?>

                <div style="min-width:260px">
                    <label class="form-label">
                        Сервер
                    </label>
                    <?= Html::dropDownList(
                        'server_id',
                        array_key_first($serverOptions),
                        $serverOptions,
                        [
                            'class' => 'form-select',
                            'required' => true,
                        ]
                    ) ?>
                </div>

                <?= Html::submitButton(
                    'Выдать OP',
                    [
                        'class' => 'btn btn-outline-primary',
                        'onclick' =>
                            "this.form.elements['operation'].value='grant';",
                        'data-confirm' =>
                            'Выдать игроку '
                            . $model->username
                            . ' полные операторские права Minecraft?',
                    ]
                ) ?>

                <?= Html::submitButton(
                    'Снять OP',
                    [
                        'class' => 'btn btn-outline-danger',
                        'onclick' =>
                            "this.form.elements['operation'].value='revoke';",
                        'data-confirm' =>
                            'Снять OP у игрока '
                            . $model->username
                            . '?',
                    ]
                ) ?>
            <?= Html::endForm() ?>

            <div class="admin-muted small mb-3">
                Команда выполняется через Delivery Queue.
                Текущее содержимое ops.json напрямую из веб-панели не читается,
                поэтому ниже показана история отправленных OP/deOP-команд.
            </div>

            <?php if(!$operatorDeliveries): ?>
                <div class="admin-muted">
                    OP-операций для этого игрока ещё не было.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Сервер</th>
                            <th>Операция</th>
                            <th>Статус</th>
                            <th>Попытки</th>
                            <th>Создано</th>
                            <th>Ошибка</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($operatorDeliveries as $delivery): ?>
                            <tr>
                                <td>
                                    #<?= (int)$delivery->id ?>
                                </td>
                                <td>
                                    <?= Html::encode(
                                        $delivery->server?->name ?: '—'
                                    ) ?>
                                </td>
                                <td>
                                    <code>
                                        <?= (string)$delivery->payload
                                            === 'op {username}'
                                                ? 'OP'
                                                : 'deOP'
                                        ?>
                                    </code>
                                </td>
                                <td>
                                    <span class="badge <?= $operatorStatusClasses[$delivery->status] ?? 'bg-secondary' ?>">
                                        <?= Html::encode(
                                            $operatorStatusLabels[$delivery->status]
                                            ?? $delivery->status
                                        ) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= (int)$delivery->attempts ?>
                                </td>
                                <td>
                                    <?= Html::encode(
                                        (string)$delivery->created_at
                                    ) ?>
                                </td>
                                <td>
                                    <?= $delivery->last_error
                                        ? Html::tag(
                                            'code',
                                            Html::encode(
                                                mb_substr(
                                                    (string)$delivery->last_error,
                                                    0,
                                                    160
                                                )
                                            ),
                                            [
                                                'class' => 'small',
                                                'title' =>
                                                    (string)$delivery->last_error,
                                            ]
                                        )
                                        : '—'
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php if(Yii::$app->user->can('viewModeration')): ?>
    <section class="admin-panel mt-3">
        <div class="admin-section-head">
            <div>
                <h2 class="admin-panel-title">
                    Игровая модерация
                </h2>
                <div class="admin-panel-subtitle">
                    Последние 10 наказаний игрока.
                </div>
            </div>

            <div class="admin-actions">
                <?= Html::a(
                    'Вся история',
                    ['/moderation/index', 'q' => $model->username],
                    ['class' => 'btn btn-sm btn-outline-primary']
                ) ?>
            </div>
        </div>

        <?php if(!$moderationActions): ?>
            <div class="admin-muted">
                Наказаний нет.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Тип</th>
                        <th>Область</th>
                        <th>Причина</th>
                        <th>Статус</th>
                        <th>До</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($moderationActions as $action): ?>
                        <tr>
                            <td>#<?= (int)$action->id ?></td>
                            <td>
                                <?= Html::encode(
                                    $moderationTypeLabels[$action->type]
                                    ?? $action->type
                                ) ?>
                            </td>
                            <td>
                                <?= $action->scope === ModerationAction::SCOPE_GLOBAL
                                    ? 'Все серверы'
                                    : Html::encode(
                                        $action->server?->name ?: 'Сервер'
                                    )
                                ?>
                            </td>
                            <td>
                                <?= Html::encode(
                                    mb_substr(
                                        (string)$action->reason,
                                        0,
                                        100
                                    )
                                ) ?>
                            </td>
                            <td>
                                <?= Html::encode(
                                    $moderationStatusLabels[$action->status]
                                    ?? $action->status
                                ) ?>
                            </td>
                            <td>
                                <?= Html::encode(
                                    $action->expires_at ?: 'Навсегда'
                                ) ?>
                            </td>
                            <td>
                                <?= Html::a(
                                    'Открыть',
                                    [
                                        '/moderation/view',
                                        'id' => $action->id,
                                    ],
                                    [
                                        'class' =>
                                            'btn btn-sm btn-outline-primary',
                                    ]
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

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

                    <?php if(
                        $canManageTarget
                        && Yii::$app->user->can(
                            'manageLauncherPermissions'
                        )
                    ): ?>
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
                                    'class' =>
                                        'btn btn-link btn-sm text-white p-0 border-0',
                                    'title' =>
                                        'Удалить permission',
                                    'data-confirm' =>
                                        'Удалить это разрешение?',
                                ]
                            ) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="admin-muted small mb-3">
            Дополнительных launcher permissions нет.
        </div>
    <?php endif; ?>

    <?php if(
        $canManageTarget
        && Yii::$app->user->can(
            'manageLauncherPermissions'
        )
    ): ?>
        <?= Html::beginForm(
            [
                '/user/add-launcher-permission',
                'id' => $model->id,
            ],
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
            <button
                class="btn btn-outline-primary"
                type="submit"
            >
                Добавить
            </button>
        <?= Html::endForm() ?>
    <?php endif; ?>
</section>
