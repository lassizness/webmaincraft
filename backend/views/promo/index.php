<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Промокоды';
?>

<div class="admin-section-head">
    <div class="admin-panel-subtitle">
        Балансовые и товарные промокоды, лимиты и сроки действия.
    </div>
    <div class="admin-actions">
        <?= Html::a(
            'Создать промокод',
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
            'attribute' => 'code',
            'contentOptions' => ['class' => 'fw-bold font-monospace'],
        ],
        'type',
        'value',
        'used_count',
        'max_uses',
        'expires_at',
        [
            'attribute' => 'enabled',
            'format' => 'raw',
            'value' => static fn($model): string => Html::tag(
                'span',
                $model->enabled ? 'Активен' : 'Выключен',
                [
                    'class' => 'badge '
                        .($model->enabled ? 'bg-success' : 'bg-secondary'),
                ]
            ),
        ],
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
