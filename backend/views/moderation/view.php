<?php

use common\models\ModerationAction;
use yii\helpers\Html;

$this->title = 'Наказание #' . $model->id;

$typeLabel = match($model->type) {
    ModerationAction::TYPE_WARN => 'Предупреждение',
    ModerationAction::TYPE_MUTE => 'Мут',
    ModerationAction::TYPE_BAN => 'Бан',
    default => $model->type,
};

$statusLabel = match($model->status) {
    ModerationAction::STATUS_ACTIVE => 'Активно',
    ModerationAction::STATUS_RECORDED => 'Записано',
    ModerationAction::STATUS_EXPIRED => 'Истекло',
    ModerationAction::STATUS_REVOKED => 'Снято',
    default => $model->status,
};

$metadata = json_decode((string)$model->metadata_json, true);
$metadata = is_array($metadata) ? $metadata : [];
?>

<div class="admin-section-head">
    <div>
        <?= Html::a(
            '← К модерации',
            ['index'],
            ['class' => 'small text-decoration-none']
        ) ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <section class="admin-panel">
            <div class="admin-detail-grid">
                <div class="admin-detail-item">
                    <div class="admin-detail-label">Игрок</div>
                    <div class="admin-detail-value">
                        <?= $model->user
                            ? Html::a(
                                Html::encode($model->user->username),
                                ['/user/view', 'id' => $model->user_id]
                            )
                            : '#' . (int)$model->user_id
                        ?>
                    </div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">Тип</div>
                    <div class="admin-detail-value"><?= Html::encode($typeLabel) ?></div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">Статус</div>
                    <div class="admin-detail-value"><?= Html::encode($statusLabel) ?></div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">Область</div>
                    <div class="admin-detail-value">
                        <?= $model->scope === 'global'
                            ? 'Все серверы'
                            : Html::encode($model->server?->name ?: 'Сервер')
                        ?>
                    </div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">Начало</div>
                    <div class="admin-detail-value"><?= Html::encode($model->starts_at) ?></div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">Окончание</div>
                    <div class="admin-detail-value">
                        <?= Html::encode($model->expires_at ?: 'Навсегда') ?>
                    </div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">Создал</div>
                    <div class="admin-detail-value">
                        <?= Html::encode($model->creator?->username ?: 'system') ?>
                    </div>
                </div>

                <div class="admin-detail-item">
                    <div class="admin-detail-label">RCON задач создано</div>
                    <div class="admin-detail-value">
                        <?= (int)($metadata['queued'] ?? 0) ?>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <div class="admin-detail-label">Причина</div>
                <div class="admin-ticket-body">
                    <?= nl2br(Html::encode((string)$model->reason)) ?>
                </div>
            </div>

            <?php if($model->internal_comment): ?>
                <div class="mt-4">
                    <div class="admin-detail-label">Внутренний комментарий</div>
                    <div class="admin-ticket-body">
                        <?= nl2br(Html::encode((string)$model->internal_comment)) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(!empty($metadata['missing'])): ?>
                <div class="alert alert-warning mt-4 mb-0">
                    Для части серверов команда этого типа не настроена:
                    <?php foreach($metadata['missing'] as $missing): ?>
                        <div class="mt-1">
                            <?= Html::encode((string)($missing['server'] ?? 'server')) ?>
                            <code><?= Html::encode((string)($missing['command'] ?? '')) ?></code>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="admin-panel mt-3">
            <h2 class="admin-panel-title mb-3">История</h2>

            <?php foreach($model->history as $row): ?>
                <div class="border-bottom py-3">
                    <div class="d-flex justify-content-between gap-3">
                        <strong><?= Html::encode($row->event_type) ?></strong>
                        <span class="admin-muted small">
                            <?= Html::encode($row->created_at) ?>
                        </span>
                    </div>
                    <div class="admin-muted small mt-1">
                        <?= Html::encode($row->actor?->username ?: 'system') ?>
                    </div>
                    <?php if($row->data_json): ?>
                        <code class="d-block small mt-2">
                            <?= Html::encode((string)$row->data_json) ?>
                        </code>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
    </div>

    <div class="col-xl-4">
        <?php if(
            Yii::$app->user->can('revokePunishment')
            && in_array(
                $model->status,
                [
                    ModerationAction::STATUS_ACTIVE,
                    ModerationAction::STATUS_RECORDED,
                ],
                true
            )
        ): ?>
            <section class="admin-panel admin-danger-zone">
                <h2 class="admin-panel-title mb-2">Снять наказание</h2>

                <?= Html::beginForm(['revoke', 'id' => $model->id], 'post') ?>
                    <textarea
                        class="form-control mb-3"
                        name="reason"
                        rows="3"
                        placeholder="Причина снятия"
                    ></textarea>

                    <?= Html::submitButton(
                        'Снять наказание',
                        [
                            'class' => 'btn btn-outline-danger w-100',
                            'data-confirm' => 'Снять наказание и поставить обратную RCON-команду в очередь?',
                        ]
                    ) ?>
                <?= Html::endForm() ?>
            </section>
        <?php endif; ?>

        <?php if($model->status === ModerationAction::STATUS_REVOKED): ?>
            <section class="admin-panel">
                <h2 class="admin-panel-title mb-3">Снятие</h2>
                <div class="small">
                    <div>
                        <?= Html::encode($model->revoked_at ?: '') ?>
                    </div>
                    <div class="admin-muted mt-1">
                        <?= Html::encode($model->revoker?->username ?: 'system') ?>
                    </div>
                    <div class="mt-3">
                        <?= nl2br(Html::encode((string)$model->revoke_reason)) ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>
