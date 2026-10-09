<?php

use yii\helpers\Html;

$this->title =
    'Moderation Bridge · ' . $server->name;

$isOnline =
    $bridge
    && (int)$bridge->enabled === 1
    && $bridge->isOnline();

$statusLabel = !$bridge
    ? 'Не настроен'
    : (
        !(int)$bridge->enabled
            ? 'Отключён'
            : ($isOnline ? 'Online' : 'Offline')
    );

$statusClass = !$bridge
    ? 'bg-secondary'
    : (
        !(int)$bridge->enabled
            ? 'bg-secondary'
            : ($isOnline ? 'bg-success' : 'bg-danger')
    );

$configLines = [
    'apiUrl=' . $apiBaseUrl,
    'serverCode=' . $server->code,
];

if($plainToken)
{
    $configLines[] = 'token=' . $plainToken;
}

$configLines[] = 'syncIntervalSeconds=3';
$configLines[] = 'connectTimeoutSeconds=3';
$configLines[] = 'readTimeoutSeconds=5';

$config = implode("\n", $configLines);
?>

<div class="admin-section-head">
    <div>
        <?= Html::a(
            '← К серверам',
            ['index'],
            ['class' => 'small text-decoration-none']
        ) ?>

        <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
            <span class="badge <?= $statusClass ?>">
                <?= Html::encode($statusLabel) ?>
            </span>

            <span class="admin-muted small">
                <?= Html::encode($server->code) ?>
            </span>
        </div>
    </div>

    <div class="admin-actions">
        <?= Html::a(
            'Настройки сервера',
            ['edit', 'id' => $server->id],
            ['class' => 'btn btn-outline-secondary']
        ) ?>
    </div>
</div>

<?php if($plainToken): ?>
    <div class="alert alert-warning">
        <strong>Сохраните токен сейчас.</strong>
        После обновления страницы полный токен больше не будет показан.
    </div>

    <section class="admin-panel mb-3">
        <h2 class="admin-panel-title mb-2">
            Конфигурация мода
        </h2>

        <div class="admin-panel-subtitle mb-3">
            Эти значения позже положим в конфиг HiveTechModeration
            на игровом сервере.
        </div>

        <pre class="p-3 rounded border bg-dark text-light mb-0"><code><?= Html::encode($config) ?></code></pre>
    </section>
<?php endif; ?>

<div class="row g-3">
    <div class="col-xl-7">
        <section class="admin-panel h-100">
            <div class="admin-section-head">
                <div>
                    <h2 class="admin-panel-title">
                        Состояние Bridge
                    </h2>
                    <div class="admin-panel-subtitle">
                        Heartbeat будет поступать от серверного мода.
                    </div>
                </div>
            </div>

            <?php if(!$bridge): ?>
                <div class="admin-muted">
                    Токен ещё не выпущен. Bridge не сможет обращаться к API.
                </div>
            <?php else: ?>
                <div class="admin-detail-grid">
                    <div class="admin-detail-item">
                        <div class="admin-detail-label">Статус</div>
                        <div class="admin-detail-value">
                            <span class="badge <?= $statusClass ?>">
                                <?= Html::encode($statusLabel) ?>
                            </span>
                        </div>
                    </div>

                    <div class="admin-detail-item">
                        <div class="admin-detail-label">Bridge version</div>
                        <div class="admin-detail-value">
                            <?= Html::encode($bridge->bridge_version ?: '—') ?>
                        </div>
                    </div>

                    <div class="admin-detail-item">
                        <div class="admin-detail-label">Minecraft</div>
                        <div class="admin-detail-value">
                            <?= Html::encode($bridge->minecraft_version ?: '—') ?>
                        </div>
                    </div>

                    <div class="admin-detail-item">
                        <div class="admin-detail-label">Forge</div>
                        <div class="admin-detail-value">
                            <?= Html::encode($bridge->forge_version ?: '—') ?>
                        </div>
                    </div>

                    <div class="admin-detail-item">
                        <div class="admin-detail-label">Revision</div>
                        <div class="admin-detail-value">
                            <?= (int)$bridge->last_revision ?>
                        </div>
                    </div>

                    <div class="admin-detail-item">
                        <div class="admin-detail-label">Наказаний в кеше</div>
                        <div class="admin-detail-value">
                            <?= (int)$bridge->cached_actions ?>
                        </div>
                    </div>

                    <div class="admin-detail-item">
                        <div class="admin-detail-label">Игроков online</div>
                        <div class="admin-detail-value">
                            <?= (int)$bridge->online_players ?>
                        </div>
                    </div>

                    <div class="admin-detail-item">
                        <div class="admin-detail-label">Последний sync</div>
                        <div class="admin-detail-value">
                            <?= Html::encode($bridge->last_sync_at ?: '—') ?>
                        </div>
                    </div>

                    <div class="admin-detail-item">
                        <div class="admin-detail-label">Heartbeat</div>
                        <div class="admin-detail-value">
                            <?= Html::encode($bridge->last_heartbeat_at ?: '—') ?>
                        </div>
                    </div>

                    <div class="admin-detail-item">
                        <div class="admin-detail-label">Последний API request</div>
                        <div class="admin-detail-value">
                            <?= Html::encode($bridge->last_used_at ?: '—') ?>
                        </div>
                    </div>

                    <div class="admin-detail-item">
                        <div class="admin-detail-label">IP</div>
                        <div class="admin-detail-value font-monospace">
                            <?= Html::encode($bridge->last_seen_ip ?: '—') ?>
                        </div>
                    </div>

                    <div class="admin-detail-item">
                        <div class="admin-detail-label">Token</div>
                        <div class="admin-detail-value font-monospace">
                            <?= Html::encode($bridge->token_hint ?: '—') ?>
                        </div>
                    </div>
                </div>

                <?php if($bridge->last_error): ?>
                    <div class="alert alert-danger mt-4 mb-0">
                        <strong>Последняя ошибка Bridge</strong>
                        <div class="mt-2">
                            <?= nl2br(Html::encode((string)$bridge->last_error)) ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>

    <div class="col-xl-5">
        <section class="admin-panel mb-3">
            <h2 class="admin-panel-title mb-2">
                API credentials
            </h2>

            <div class="admin-panel-subtitle mb-3">
                У каждого игрового сервера свой токен.
                В базе хранится только SHA-256.
            </div>

            <dl class="row small mb-3">
                <dt class="col-5">API</dt>
                <dd class="col-7 font-monospace">
                    <?= Html::encode($apiBaseUrl) ?>
                </dd>

                <dt class="col-5">Server code</dt>
                <dd class="col-7 font-monospace">
                    <?= Html::encode($server->code) ?>
                </dd>

                <dt class="col-5">Token hint</dt>
                <dd class="col-7 font-monospace">
                    <?= Html::encode($bridge?->token_hint ?: '—') ?>
                </dd>

                <dt class="col-5">Rotated</dt>
                <dd class="col-7">
                    <?= Html::encode($bridge?->rotated_at ?: '—') ?>
                </dd>
            </dl>

            <?= Html::beginForm(
                ['rotate-bridge-token', 'id' => $server->id],
                'post'
            ) ?>
                <?= Html::submitButton(
                    $bridge
                        ? 'Ротировать токен'
                        : 'Выпустить токен',
                    [
                        'class' => $bridge
                            ? 'btn btn-outline-warning w-100'
                            : 'btn btn-primary w-100',
                        'data-confirm' => $bridge
                            ? 'Старый токен немедленно перестанет работать. Продолжить?'
                            : null,
                    ]
                ) ?>
            <?= Html::endForm() ?>

            <?php if($bridge): ?>
                <div class="mt-2">
                    <?= Html::beginForm(
                        ['toggle-bridge', 'id' => $server->id],
                        'post'
                    ) ?>
                        <input
                            type="hidden"
                            name="enabled"
                            value="<?= (int)!((bool)$bridge->enabled) ?>"
                        >

                        <?= Html::submitButton(
                            $bridge->enabled
                                ? 'Отключить Bridge API'
                                : 'Включить Bridge API',
                            [
                                'class' => $bridge->enabled
                                    ? 'btn btn-outline-danger w-100'
                                    : 'btn btn-outline-success w-100',
                                'data-confirm' => $bridge->enabled
                                    ? 'Мод потеряет доступ к Moderation API. Отключить?'
                                    : null,
                            ]
                        ) ?>
                    <?= Html::endForm() ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="admin-panel">
            <h2 class="admin-panel-title mb-2">
                Контракт API v1
            </h2>

            <div class="admin-panel-subtitle mb-3">
                Этот контракт будет использовать отдельный Forge-мод
                HiveTechModeration.
            </div>

            <div class="d-grid gap-2 small font-monospace">
                <div>GET /moderation/snapshot</div>
                <div>GET /moderation/changes?after=REV</div>
                <div>GET /moderation/player/{uuid}</div>
                <div>POST /bridge/heartbeat</div>
            </div>

            <hr>

            <div class="small">
                Headers:
                <pre class="mt-2 mb-0"><code>Authorization: Bearer &lt;token&gt;
X-HiveTech-Server: <?= Html::encode($server->code) ?></code></pre>
            </div>
        </section>
    </div>
</div>
