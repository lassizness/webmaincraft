<?php

use common\models\SupportTicket;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Тикет #' . $ticket->id;

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

<div class="admin-section-head">
    <div>
        <?= Html::a(
            '← К обращениям',
            ['/support/index'],
            ['class' => 'small text-decoration-none']
        ) ?>
        <div class="d-flex gap-2 align-items-center mt-2 flex-wrap">
            <span class="badge <?= $statusClasses[$ticket->status] ?? 'bg-secondary' ?>">
                <?= Html::encode(
                    $statusLabels[$ticket->status] ?? $ticket->status
                ) ?>
            </span>

            <?php if($ticket->user): ?>
                <?= Html::a(
                    '@' . Html::encode($ticket->user->username),
                    ['/user/view', 'id' => $ticket->user_id],
                    ['class' => 'small text-decoration-none']
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if($ticket->status !== SupportTicket::STATUS_CLOSED): ?>
        <div class="admin-actions">
            <?= Html::beginForm(
                ['/support/close', 'id' => $ticket->id],
                'post'
            ) ?>
                <?= Html::submitButton(
                    'Закрыть тикет',
                    [
                        'class' => 'btn btn-sm btn-outline-danger',
                        'data-confirm' => 'Закрыть тикет? Пользователь больше не сможет продолжить этот диалог.',
                    ]
                ) ?>
            <?= Html::endForm() ?>
        </div>
    <?php endif; ?>
</div>

<section class="admin-panel mb-3">
    <div class="admin-section-head">
        <div>
            <h2 class="admin-panel-title">
                <?= Html::encode($ticket->subject) ?>
            </h2>
            <div class="admin-panel-subtitle">
                Последнее обновление:
                <?= Html::encode((string)$ticket->updated_at) ?>
            </div>
        </div>
    </div>

    <div class="admin-ticket-thread">
        <?php foreach($ticket->messages as $message): ?>
            <article class="admin-ticket-message<?= $message->is_staff ? ' is-staff' : '' ?>">
                <div class="admin-ticket-meta">
                    <?= $message->is_staff
                        ? 'Команда поддержки'
                        : Html::encode(
                            $message->author?->username ?: 'Пользователь'
                        )
                    ?>
                    ·
                    <?= Html::encode($message->created_at) ?>
                </div>

                <div class="admin-ticket-body">
                    <?= nl2br(Html::encode($message->body)) ?>
                </div>

                <?php if($message->attachments): ?>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <?php foreach($message->attachments as $attachment): ?>
                            <?= Html::a(
                                '↓ ' . Html::encode(
                                    $attachment->original_name
                                ),
                                [
                                    'attachment',
                                    'id' => $attachment->id,
                                ],
                                [
                                    'class' => 'btn btn-sm btn-outline-secondary',
                                ]
                            ) ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php if($ticket->status !== SupportTicket::STATUS_CLOSED): ?>
    <section class="admin-panel">
        <h2 class="admin-panel-title mb-1">
            Ответить пользователю
        </h2>
        <div class="admin-panel-subtitle mb-3">
            После отправки тикет перейдёт в статус «Отвечен», а пользователю уйдёт email.
        </div>

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'message')
            ->textarea([
                'rows' => 6,
                'placeholder' => 'Напиши понятный ответ без внутренних технических деталей...',
            ])
            ->label('Ответ') ?>

        <?= Html::submitButton(
            'Отправить ответ',
            ['class' => 'btn btn-primary']
        ) ?>

        <?php ActiveForm::end(); ?>
    </section>
<?php else: ?>
    <div class="alert alert-info">
        Тикет закрыт. История сохранена для аудита.
    </div>
<?php endif; ?>
