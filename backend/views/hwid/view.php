<?php

use yii\helpers\Html;

/** @var common\models\Hwid $model */

$this->title = 'HWID #' . $model->id;
?>

<div class="row g-3">
    <div class="col-xl-7">
        <section class="admin-panel">
            <div class="admin-section-head">
                <div>
                    <h2 class="admin-panel-title">Устройство</h2>
                    <div class="admin-panel-subtitle">Аппаратные признаки, полученные от GravitLauncher.</div>
                </div>
                <div class="admin-actions">
                    <?= Html::beginForm(['ban', 'id' => $model->id, 'value' => $model->banned ? 0 : 1], 'post') ?>
                    <?= Html::submitButton(
                        $model->banned ? 'Разблокировать HWID' : 'Заблокировать HWID',
                        [
                            'class' => $model->banned ? 'btn btn-success' : 'btn btn-outline-danger',
                            'data-confirm' => $model->banned
                                ? null
                                : 'Заблокировать это устройство и сбросить launcher-токены связанных аккаунтов?',
                        ]
                    ) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>

            <div class="admin-detail-grid">
                <?php foreach([
                    'Диск' => $model->hwDiskId,
                    'Материнская плата' => $model->baseboardSerialNumber,
                    'Видеокарта' => $model->graphicCard,
                    'Разрядность' => $model->bitness,
                    'Логических CPU' => $model->logicalProcessors,
                    'Физических CPU' => $model->physicalProcessors,
                    'Макс. частота' => $model->processorMaxFreq,
                    'RAM' => $model->totalMemory,
                    'Батарея' => $model->battery ? 'Да' : 'Нет',
                ] as $label => $value): ?>
                    <div class="admin-detail-item">
                        <div class="admin-detail-label"><?= Html::encode($label) ?></div>
                        <div class="admin-detail-value"><?= Html::encode((string)($value ?? '—')) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="admin-panel mt-3">
            <h2 class="admin-panel-title mb-3">Связанные аккаунты</h2>

            <?php if(!$model->users): ?>
                <div class="admin-muted">Нет активных привязок.</div>
            <?php endif; ?>

            <?php foreach($model->users as $user): ?>
                <div class="d-flex justify-content-between align-items-center gap-3 border-bottom py-3">
                    <div>
                        <strong><?= Html::encode((string)$user->username) ?></strong>
                        <div class="admin-muted small">#<?= (int)$user->id ?> · <?= Html::encode((string)$user->uuid) ?></div>
                    </div>
                    <?= Html::beginForm(['unbind', 'userId' => $user->id], 'post') ?>
                    <?= Html::submitButton('Отвязать HWID', [
                        'class' => 'btn btn-sm btn-outline-danger',
                        'data-confirm' => 'Отвязать устройство и сбросить launcher-токены этого пользователя?',
                    ]) ?>
                    <?= Html::endForm() ?>
                </div>
            <?php endforeach; ?>
        </section>
    </div>

    <div class="col-xl-5">
        <section class="admin-panel h-100">
            <h2 class="admin-panel-title mb-3">История ключей</h2>

            <?php if(!$model->logs): ?>
                <div class="admin-muted">История пуста.</div>
            <?php endif; ?>

            <?php foreach($model->logs as $log): ?>
                <div class="mb-3">
                    <div class="admin-muted small">Запись #<?= (int)$log->id ?></div>
                    <code class="small"><?= Html::encode(
                        substr(hash('sha256', (string)$log->newPublicKey), 0, 32)
                    ) ?>…</code>
                </div>
            <?php endforeach; ?>
        </section>
    </div>
</div>
