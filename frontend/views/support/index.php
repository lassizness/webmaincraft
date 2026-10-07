<?php

use yii\helpers\{Html, Url};

$this->title = Yii::t('ui', 'Поддержка');

$statusLabel = static function(string $status): string
{
    return Yii::t('ui', match($status)
    {
        'open' => 'Открыт',
        'answered' => 'Есть ответ',
        'closed' => 'Закрыт',
        default => $status,
    });
};

?>

<div class="container ht-section">
    <div class="account-grid">

        <?= $this->render('../account/_nav') ?>

        <section>

            <div class="section-title">

                <h2>
                    <?= Yii::t('ui', 'Поддержка') ?>
                </h2>

                <a
                    class="btn btn-primary"
                    href="<?= Url::to(['/support/create']) ?>"
                >
                    <?= Yii::t('ui', 'Новый тикет') ?>
                </a>

            </div>

            <div class="ht-card">

                <div class="table-responsive">
                <table class="table">

                    <thead>
                    <tr>
                        <th>ID</th>
                        <th><?= Yii::t('ui', 'Тема') ?></th>
                        <th><?= Yii::t('ui', 'Статус') ?></th>
                        <th><?= Yii::t('ui', 'Обновлён') ?></th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php foreach($tickets as $ticket): ?>
                        <tr>
                            <td>#<?= $ticket->id ?></td>

                            <td>
                                <a href="<?= Url::to([
                                    '/support/view',
                                    'id' => $ticket->id,
                                ]) ?>">
                                    <?= Html::encode($ticket->subject) ?>
                                </a>
                            </td>

                            <td>
                                <?= Html::encode(
                                    $statusLabel($ticket->status)
                                ) ?>
                            </td>

                            <td>
                                <?= Html::encode($ticket->updated_at) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>
                </div>

                <?php if(!$tickets): ?>

                    <p class="muted">
                        <?= Yii::t(
                            'ui',
                            'Обращений пока нет.'
                        ) ?>
                    </p>

                <?php endif; ?>

            </div>

        </section>

    </div>
</div>
