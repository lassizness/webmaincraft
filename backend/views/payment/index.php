<?php

use common\models\PaymentOrder;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Платежи';

$statusLabels = [
    PaymentOrder::STATUS_NEW => 'Новый',
    PaymentOrder::STATUS_PAID => 'Оплачен',
    PaymentOrder::STATUS_CANCELLED => 'Отменён',
    PaymentOrder::STATUS_FAILED => 'Ошибка',
];

$statusClasses = [
    PaymentOrder::STATUS_NEW => 'bg-warning',
    PaymentOrder::STATUS_PAID => 'bg-success',
    PaymentOrder::STATUS_CANCELLED => 'bg-secondary',
    PaymentOrder::STATUS_FAILED => 'bg-danger',
];
?>

<div class="alert alert-warning mb-3">
    Подтверждение нового платежа начисляет баланс пользователю.
    Проверяй сумму и ID аккаунта перед действием.
</div>

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
            'label' => 'Пользователь',
            'format' => 'raw',
            'value' => static fn(PaymentOrder $model): string =>
                Html::a(
                    '#' . $model->user_id,
                    ['/user/view', 'id' => $model->user_id],
                    ['class' => 'text-decoration-none']
                ),
        ],
        'provider',
        [
            'attribute' => 'amount',
            'label' => 'Сумма',
            'value' => static fn(PaymentOrder $model): string =>
                number_format((float)$model->amount, 2, '.', ' '),
            'contentOptions' => ['class' => 'fw-bold'],
        ],
        [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => static fn(PaymentOrder $model): string =>
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
        'created_at',
        [
            'label' => '',
            'format' => 'raw',
            'value' => static function(PaymentOrder $model): string {
                if($model->status !== PaymentOrder::STATUS_NEW)
                {
                    return '';
                }

                $approve = Html::beginForm(
                    ['/payment/approve', 'id' => $model->id],
                    'post',
                    ['class' => 'd-inline']
                )
                .Html::submitButton(
                    'Подтвердить',
                    [
                        'class' => 'btn btn-sm btn-success',
                        'data-confirm' => 'Подтвердить платёж #' . $model->id
                            . ' на сумму ' . $model->amount
                            . '? Баланс пользователя будет пополнен.',
                    ]
                )
                .Html::endForm();

                $cancel = Html::beginForm(
                    ['/payment/cancel', 'id' => $model->id],
                    'post',
                    ['class' => 'd-inline']
                )
                .Html::submitButton(
                    'Отклонить',
                    [
                        'class' => 'btn btn-sm btn-outline-danger',
                        'data-confirm' => 'Отклонить платёж #' . $model->id . '?',
                    ]
                )
                .Html::endForm();

                return '<div class="d-flex gap-2 justify-content-end">'
                    .$approve
                    .$cancel
                    .'</div>';
            },
        ],
    ],
]) ?>
