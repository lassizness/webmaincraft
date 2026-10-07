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
            'template' => '{update}',
            'buttons' => [
                'update' => static fn($url, $model): string =>
                    Html::a(
                        'Изменить',
                        ['edit', 'id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-primary']
                    ),
            ],
        ],
    ],
]) ?>
