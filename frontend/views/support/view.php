<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t(
    'ui',
    'Тикет #{id}',
    ['id' => $ticket->id]
);

$statusLabel = Yii::t('ui', match($ticket->status)
{
    'open' => 'Открыт',
    'answered' => 'Есть ответ',
    'closed' => 'Закрыт',
    default => $ticket->status,
});

?>

<div class="container ht-section">
    <div class="account-grid">

        <?= $this->render('../account/_nav') ?>

        <section>
            <div class="ht-card">

                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">

                    <h2>
                        #<?= $ticket->id ?>
                        ·
                        <?= Html::encode($ticket->subject) ?>
                    </h2>

                    <span>
                        <?= Html::encode($statusLabel) ?>
                    </span>

                </div>

                <?php foreach($ticket->messages as $message): ?>

                    <div class="ticket-msg <?= $message->is_staff
                        ? 'staff'
                        : ''
                    ?>">

                        <div class="small muted mb-2">

                            <?php if($message->is_staff): ?>

                                <?= Yii::t('ui', 'Поддержка') ?>

                            <?php else: ?>

                                <?= Html::encode(
                                    $message->author?->username
                                    ?: Yii::t('ui', 'Игрок')
                                ) ?>

                            <?php endif; ?>

                            · <?= Html::encode($message->created_at) ?>

                        </div>

                        <?= nl2br(Html::encode($message->body)) ?>

                        <?php if($message->attachments): ?>

                            <div class="mt-2">

                                <?php foreach(
                                    $message->attachments as $attachment
                                ): ?>

                                    <a
                                        class="small me-2"
                                        href="<?= \yii\helpers\Url::to([
                                            '/support/attachment',
                                            'id' => $attachment->id,
                                        ]) ?>"
                                    >
                                        <?= Html::encode(
                                            $attachment->original_name
                                        ) ?>
                                    </a>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

                <?php if(
                    $ticket->status
                    !== \common\models\SupportTicket::STATUS_CLOSED
                ): ?>

                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form
                        ->field($model, 'message')
                        ->textarea(['rows' => 5])
                        ->label(Yii::t('ui', 'Ответ')) ?>

                    <?= Html::submitButton(
                        Yii::t('ui', 'Отправить'),
                        ['class' => 'btn btn-primary']
                    ) ?>

                    <?php ActiveForm::end(); ?>

                <?php endif; ?>

            </div>
        </section>

    </div>
</div>
