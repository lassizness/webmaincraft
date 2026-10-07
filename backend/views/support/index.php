<?php

use common\models\SupportTicket;
use yii\grid\GridView;
use yii\helpers\{Html, Url};

$this->title = 'Поддержка';

$statusLabels = [
    SupportTicket::STATUS_OPEN => 'Открыт',
    SupportTicket::STATUS_ANSWERED => 'Отвечен',
    SupportTicket::STATUS_CLOSED => 'Закрыт',
];

$statusClasses = [
    SupportTicket::STATUS_OPEN => 'bg-warning',
    SupportTicket::STATUS_ANSWERED => 'bg-info',
    SupportTicket::STATUS_CLOSED => 'bg-secondary',
];
?>

<section class="admin-panel mb-3">
    <div class="admin-section-head mb-0">
        <div>
            <h2 class="admin-panel-title">Очередь обращений</h2>
            <div class="admin-panel-subtitle">
                Сначала разбирай открытые и недавно обновлённые тикеты.
            </div>
        </div>
    </div>
</section>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => [
        'class' => 'table table-hover align-middle',
    ],
    'columns' => [
        [
            'attribute' => 'id',
            'contentOptions' => ['class' => 'text-secondary'],
        ],
        [
            'attribute' => 'user_id',
            'label' => 'Игрок',
            'format' => 'raw',
            'value' => static function(SupportTicket $model): string {
                if(!$model->user)
                {
                    return '#' . $model->user_id;
                }

                return Html::a(
                    Html::encode($model->user->username),
                    ['/user/view', 'id' => $model->user_id],
                    ['class' => 'text-decoration-none']
                );
            },
        ],
        [
            'attribute' => 'subject',
            'label' => 'Тема',
            'format' => 'raw',
            'value' => static fn(SupportTicket $model): string =>
                Html::a(
                    Html::encode($model->subject),
                    ['/support/view', 'id' => $model->id],
                    ['class' => 'fw-bold text-decoration-none']
                ),
        ],
        [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => static fn(SupportTicket $model): string =>
                Html::tag(
                    'span',
                    Html::encode(
                        $statusLabels[$model->status] ?? $model->status
                    ),
                    [
                        'class' => 'badge '
                            .($statusClasses[$model->status]
                                ?? 'bg-secondary'
                            ),
                    ]
                ),
        ],
        'updated_at',
        [
            'class' => yii\grid\ActionColumn::class,
            'header' => '',
            'template' => '{view}',
            'buttons' => [
                'view' => static fn($url, SupportTicket $model): string =>
                    Html::a(
                        'Открыть',
                        ['/support/view', 'id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-primary']
                    ),
            ],
        ],
    ],
]) ?>
