<?php

use yii\helpers\{Html, Url};

$this->title = 'Игровая аналитика';

$metrics = [
    ['label' => 'Online', 'value' => $onlinePlayers, 'note' => 'активные игровые сессии'],
    ['label' => 'Событий за 5 минут', 'value' => $events5m, 'note' => 'последний поток телеметрии'],
    ['label' => 'Событий сегодня', 'value' => $eventsToday, 'note' => 'raw telemetry'],
    ['label' => 'Очивок сегодня', 'value' => $unlocksToday, 'note' => 'разблокировано игроками'],
    ['label' => 'Последнее событие', 'value' => $lastEventAt ?: '—', 'note' => 'время приёма backend'],
];
?>

<div class="admin-kpi-grid mb-4">
    <?php foreach($metrics as $metric): ?>
        <div class="admin-kpi">
            <div class="admin-kpi-label">
                <?= Html::encode($metric['label']) ?>
            </div>
            <div class="admin-kpi-value" style="<?= is_numeric($metric['value']) ? '' : 'font-size:18px' ?>">
                <?= Html::encode((string)$metric['value']) ?>
            </div>
            <div class="admin-kpi-note">
                <?= Html::encode($metric['note']) ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="admin-section-head">
    <div>
        <h2 class="admin-panel-title">Состояние HiveTechTelemetry</h2>
        <div class="admin-panel-subtitle">
            Сервер считается online, если heartbeat был не более 120 секунд назад.
        </div>
    </div>
    <div class="admin-actions">
        <a class="btn btn-outline-primary" href="<?= Url::to(['/telemetry/events']) ?>">
            События игры
        </a>
    </div>
</div>

<section class="admin-panel p-0 overflow-hidden">
    <?php if(!$serverRows): ?>
        <div class="admin-muted p-3">
            Нет включённых Minecraft-серверов.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                <tr>
                    <th>Сервер</th>
                    <th>Статус</th>
                    <th>Telemetry</th>
                    <th>Minecraft / Forge</th>
                    <th>Online</th>
                    <th>Queue</th>
                    <th>Buffered</th>
                    <th>Последний batch</th>
                    <th>Всего событий</th>
                    <th>IP</th>
                    <th>Ошибка</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($serverRows as $row): ?>
                    <?php
                    $heartbeatAt = $row['last_heartbeat_at']
                        ? strtotime((string)$row['last_heartbeat_at'])
                        : false;
                    $online = $heartbeatAt !== false
                        && $heartbeatAt >= time() - 120;
                    ?>
                    <tr>
                        <td>
                            <strong><?= Html::encode((string)$row['server_name']) ?></strong>
                            <div class="small admin-muted">
                                <code><?= Html::encode((string)$row['server_code']) ?></code>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $online ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $online ? 'Online' : 'Offline' ?>
                            </span>
                            <?php if($row['last_heartbeat_at']): ?>
                                <div class="small admin-muted mt-1">
                                    <?= Html::encode((string)$row['last_heartbeat_at']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= Html::encode((string)($row['telemetry_version'] ?: '—')) ?>
                        </td>
                        <td>
                            <div>
                                <?= Html::encode((string)($row['minecraft_version'] ?: '—')) ?>
                            </div>
                            <div class="small admin-muted">
                                <?= Html::encode((string)($row['forge_version'] ?: '—')) ?>
                            </div>
                        </td>
                        <td><?= (int)$row['online_players'] ?></td>
                        <td><?= (int)$row['queue_size'] ?></td>
                        <td><?= (int)$row['buffered_events'] ?></td>
                        <td>
                            <?= Html::encode((string)($row['last_batch_at'] ?: '—')) ?>
                            <div class="small admin-muted">
                                size: <?= (int)$row['last_batch_size'] ?>
                            </div>
                        </td>
                        <td>
                            <?= number_format(
                                (int)$row['events_received_total'],
                                0,
                                ',',
                                ' '
                            ) ?>
                        </td>
                        <td>
                            <code><?= Html::encode((string)($row['last_seen_ip'] ?: '—')) ?></code>
                        </td>
                        <td>
                            <?php if($row['last_error']): ?>
                                <code
                                    class="small"
                                    title="<?= Html::encode((string)$row['last_error']) ?>"
                                >
                                    <?= Html::encode(
                                        mb_substr(
                                            (string)$row['last_error'],
                                            0,
                                            120
                                        )
                                    ) ?>
                                </code>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
