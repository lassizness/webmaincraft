<?php

use common\models\UserPrivilege;
use yii\helpers\{Html, Url};

$this->title = Yii::t('ui', 'Игровой статус');
?>

<div class="container ht-section">
    <div class="account-grid">

        <?= $this->render('../account/_nav') ?>

        <section>
            <div class="section-title">
                <h2><?= Yii::t('ui', 'Игровой статус') ?></h2>

                <a class="btn btn-primary" href="<?= Url::to(['/shop/index']) ?>">
                    <?= Yii::t('ui', 'Магазин') ?>
                </a>
            </div>

            <?php if(!$privileges): ?>
                <div class="ht-card">
                    <p class="muted mb-0">
                        <?= Yii::t('ui', 'Платных статусов пока нет.') ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach($privileges as $row): ?>
                        <?php
                        $active = $row->status === UserPrivilege::STATUS_ACTIVE
                            && strtotime((string)$row->active_to) > time();

                        $seconds = $active
                            ? max(0, strtotime((string)$row->active_to) - time())
                            : 0;

                        $days = $active
                            ? (int)ceil($seconds / 86400)
                            : 0;

                        $products = $renewalProducts[(int)$row->id] ?? [];
                        ?>

                        <div class="col-lg-6">
                            <div class="ht-card h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="muted">
                                            <?= Html::encode(
                                                $row->server?->name
                                                ?: Yii::t('ui', 'Сервер')
                                            ) ?>
                                        </div>

                                        <h3
                                            class="mt-2"
                                            style="color: <?= Html::encode(
                                                $row->group?->color ?: '#ffffff'
                                            ) ?>"
                                        >
                                            <?= Html::encode(
                                                $row->group?->name
                                                ?: Yii::t('ui', 'Статус')
                                            ) ?>
                                        </h3>
                                    </div>

                                    <span class="badge <?= $active
                                        ? 'text-bg-success'
                                        : 'text-bg-secondary'
                                    ?>">
                                        <?= $active
                                            ? Yii::t('ui', 'Активен')
                                            : Html::encode(
                                                Yii::t('ui', match($row->status) {
                                                    UserPrivilege::STATUS_EXPIRED => 'Истёк',
                                                    UserPrivilege::STATUS_REVOKED => 'Отозван',
                                                    default => $row->status,
                                                })
                                            )
                                        ?>
                                    </span>
                                </div>

                                <hr>

                                <div class="small">
                                    <?= Yii::t('ui', 'Начало:') ?>
                                    <strong><?= Html::encode($row->active_from) ?></strong>
                                </div>

                                <div class="small mt-2">
                                    <?= Yii::t('ui', 'До:') ?>
                                    <strong><?= Html::encode($row->active_to) ?></strong>
                                </div>

                                <?php if($active): ?>
                                    <div class="metric mt-3">
                                        <?= $days ?> <?= Yii::t('ui', 'дн.') ?>
                                    </div>
                                    <div class="muted">
                                        <?= Yii::t('ui', 'осталось') ?>
                                    </div>

                                    <?php if($days <= 7): ?>
                                        <div class="alert alert-warning mt-3 mb-0">
                                            <?= Yii::t(
                                                'ui',
                                                'Статус заканчивается через {days} дн.',
                                                ['days' => $days]
                                            ) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php elseif($row->status === UserPrivilege::STATUS_EXPIRED): ?>
                                    <div class="alert alert-info mt-3 mb-0">
                                        <?= Yii::t(
                                            'ui',
                                            'Статус уже закончился. Его можно активировать снова.'
                                        ) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if($products): ?>
                                    <div class="mt-4">
                                        <div class="fw-semibold mb-1">
                                            <?= Yii::t('ui', 'Продлить') ?>
                                        </div>

                                        <?php if($active): ?>
                                            <div class="muted small mb-3">
                                                <?= Yii::t(
                                                    'ui',
                                                    'Продление добавит новый срок к текущей дате окончания.'
                                                ) ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach($products as $product): ?>
                                                <?= Html::beginForm(
                                                    ['/privilege/renew', 'productId' => $product->id],
                                                    'post',
                                                    ['class' => 'd-inline']
                                                ) ?>

                                                <?= Html::submitButton(
                                                    Yii::t(
                                                        'ui',
                                                        'Продлить на {days} дней · {price} кварков',
                                                        [
                                                            'days' => (int)$product->duration_days,
                                                            'price' => number_format(
                                                                (float)$product->price,
                                                                0,
                                                                ',',
                                                                ' '
                                                            ),
                                                        ]
                                                    ),
                                                    [
                                                        'class' => 'btn btn-outline-light btn-sm',
                                                        'data-confirm' => Yii::t(
                                                            'ui',
                                                            'Подтвердить продление статуса?'
                                                        ),
                                                    ]
                                                ) ?>

                                                <?= Html::endForm() ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
