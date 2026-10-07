<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Счётчики посещений';
?>

<div class="admin-section-head">
    <div class="admin-panel-subtitle">
        Внешние счётчики и аналитические скрипты публичного сайта.
    </div>
    <div class="admin-actions">
        <?= Html::a(
            'Добавить счётчик',
            ['create'],
            ['class' => 'btn btn-primary']
        ) ?>
    </div>
</div>

<div class="card p-3">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,

        'columns' => [
            'id',

            'name',

            [
                'label' => 'Установлен в',
                'value' => static function ($model): string {
                    $positions = [];

                    if (trim((string)$model->head_code) !== '') {
                        $positions[] = 'HEAD';
                    }

                    if (trim((string)$model->body_start_code) !== '') {
                        $positions[] = 'BODY ↑';
                    }

                    if (trim((string)$model->body_end_code) !== '') {
                        $positions[] = 'BODY ↓';
                    }

                    return $positions
                        ? implode(', ', $positions)
                        : '—';
                },
            ],

            [
                'attribute' => 'enabled',
                'format' => 'raw',
                'value' => static function ($model): string {
                    return $model->enabled
                        ? '<span class="badge bg-success">Включён</span>'
                        : '<span class="badge bg-secondary">Выключен</span>';
                },
            ],

            'sort',

            [
                'class' => yii\grid\ActionColumn::class,
                'template' => '{update} {delete}',
            ],
        ],
    ]) ?>
</div>
