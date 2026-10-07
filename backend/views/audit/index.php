<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Аудит';
?>

<div class="admin-section-head">
    <div class="admin-panel-subtitle">
        Последние административные и пользовательские действия.
        Записи доступны только для чтения.
    </div>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle'],
    'columns' => [
        [
            'attribute' => 'id',
            'contentOptions' => ['class' => 'text-secondary'],
        ],
        [
            'attribute' => 'user_id',
            'label' => 'Пользователь',
            'format' => 'raw',
            'value' => static fn($model): string => $model->user_id
                ? Html::a(
                    '#' . $model->user_id,
                    ['/user/view', 'id' => $model->user_id],
                    ['class' => 'text-decoration-none']
                )
                : '—',
        ],
        [
            'attribute' => 'action',
            'contentOptions' => ['class' => 'fw-bold'],
        ],
        'entity_type',
        'entity_id',
        'ip_address',
        'created_at',
        [
            'attribute' => 'data_json',
            'label' => 'Данные',
            'format' => 'raw',
            'value' => static function($model): string {
                $value = trim((string)$model->data_json);

                if($value === '')
                {
                    return '—';
                }

                $short = mb_strlen($value) > 180
                    ? mb_substr($value, 0, 180) . '…'
                    : $value;

                return Html::tag(
                    'code',
                    Html::encode($short),
                    [
                        'class' => 'admin-code small',
                        'title' => $value,
                    ]
                );
            },
        ],
    ],
]) ?>
