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
        <h2 class="admin-panel-title">Состояние телеметрии</h2>
        <div class="admin-panel-subtitle">
            Пока HiveTechTelemetry не подключён, здесь будет пустое состояние.
            После запуска мода серверы появятся автоматически по входящим событиям.
        </div>
    </div>
    <div class="admin-actions">
        <a class="btn btn-outline-primary" href="<?= Url::to(['/telemetry/events']) ?>">
            Каталог событий
        </a>
    </div>
</div>

<section class="admin-panel">
    <?php if(!$serverRows): ?>
        <div class="admin-muted">
            Событий ещё нет. Схема backend готова к приёму HiveTechTelemetry.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Сервер</th>
                    <th>Код</th>
                    <th>Событий / 5 мин</th>
                    <th>Всего</th>
                    <th>Последнее событие</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($serverRows as $row): ?>
                    <tr>
                        <td><?= Html::encode((string)($row['server_name'] ?: 'Удалённый сервер')) ?></td>
                        <td><code><?= Html::encode((string)($row['server_code'] ?: '—')) ?></code></td>
                        <td><?= (int)$row['events_5m'] ?></td>
                        <td><?= number_format((int)$row['events_total'], 0, ',', ' ') ?></td>
                        <td><?= Html::encode((string)$row['last_event']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
