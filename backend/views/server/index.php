<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Серверы';
?>

<div class="admin-section-head">
    <div class="admin-panel-subtitle">
        Игровые серверы, их адреса и RCON-привязки.
    </div>
    <div class="admin-actions">
        <?= Html::a(
            'Добавить сервер',
            ['edit'],
            ['class' => 'btn btn-primary']
        ) ?>
    </div>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle'],
    'columns' => [
        'id',
        [
            'attribute' => 'name',
            'contentOptions' => ['class' => 'fw-bold'],
        ],
        'code',
        'host',
        'port',
        'rcon_env_key',
        [
            'label' => 'Bridge',
            'format' => 'raw',
            'value' => static function($model): string {
                $bridge = $model->moderationBridge;

                if(!$bridge)
                {
                    return Html::a(
                        'Не настроен',
                        ['bridge', 'id' => $model->id],
                        ['class' => 'badge bg-secondary text-decoration-none']
                    );
                }

                $online = (int)$bridge->enabled === 1
                    && $bridge->isOnline();

                $label = !(int)$bridge->enabled
                    ? 'Отключён'
                    : ($online ? 'Online' : 'Offline');

                $class = !(int)$bridge->enabled
                    ? 'bg-secondary'
                    : ($online ? 'bg-success' : 'bg-danger');

                return Html::a(
                    $label,
                    ['bridge', 'id' => $model->id],
                    ['class' => 'badge '.$class.' text-decoration-none']
                );
            },
        ],
        [
            'attribute' => 'enabled',
            'format' => 'raw',
            'value' => static fn($model): string => Html::tag(
                'span',
                $model->enabled ? 'Включён' : 'Выключен',
                [
                    'class' => 'badge '
                        .($model->enabled ? 'bg-success' : 'bg-secondary'),
                ]
            ),
        ],
        'sort',
        [
            'class' => yii\grid\ActionColumn::class,
            'template' => '{update} {bridge}',
            'buttons' => [
                'update' => static fn($url, $model): string =>
                    Html::a(
                        'Изменить',
                        ['edit', 'id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-primary me-1']
                    ),
                'bridge' => static fn($url, $model): string =>
                    Html::a(
                        'Bridge',
                        ['bridge', 'id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-secondary']
                    ),
            ],
        ],
    ],
]) ?>
