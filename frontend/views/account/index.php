<?php

use yii\helpers\{Html, Url};

$this->title = Yii::t('ui', 'Личный кабинет');

$status = Yii::t(
    'ui',
    (string)$user->getDisplayStatus()
);

?>

<div class="container ht-section">
    <div class="account-grid">

        <?= $this->render('_nav') ?>

        <section>

            <div class="section-title">
                <h2>
                    <?= Yii::t('ui', 'Привет, {username}', [
                        'username' => Html::encode($user->username),
                    ]) ?>
                </h2>

                <span class="badge text-bg-success">
                    <?= Html::encode($status) ?>
                </span>
            </div>

            <div class="row g-3">

                <div class="col-md-6">
                    <div class="ht-card">
                        <div class="muted">UUID</div>
                        <div class="small mt-2 font-monospace">
                            <?= Html::encode($user->uuid) ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="ht-card">
                        <div class="muted">
                            <?= Yii::t('ui', 'Баланс') ?>
                        </div>

                        <div class="metric">
                            <?= number_format(
                                (float)$wallet->balance,
                                2,
                                ',',
                                ' '
                            ) ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="ht-card">
                        <div class="muted">Email</div>
                        <div class="mt-2">
                            <?= Html::encode($user->email) ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="ht-card mt-4">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <h3 class="mb-1">
                            <?= Yii::t('ui', 'Голосование за сервер') ?>
                        </h3>
                        <p class="muted mb-0">
                            <?= Yii::t('ui', 'Поддержи HiveGrid голосом и получай кварки.') ?>
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap justify-content-end">
                        <?php foreach (($voteServices ?? []) as $voteService): ?>
                            <a
                                class="btn btn-outline-light"
                                href="<?= Url::to(['/vote/index']) ?>#vote-service-<?= Html::encode((string)$voteService->code) ?>"
                            >
                                <?= Html::encode((string)$voteService->name) ?>
                            </a>
                        <?php endforeach; ?>

                        <a class="btn btn-primary" href="<?= Url::to(['/vote/index']) ?>">
                            <?= Yii::t('ui', 'Все рейтинги') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="ht-card mt-4">

                <h3>
                    <?= Yii::t('ui', 'Последние покупки') ?>
                </h3>

                <?php if(!$purchases): ?>

                    <p class="muted">
                        <?= Yii::t('ui', 'Покупок пока нет.') ?>
                    </p>

                <?php else: ?>

                    <table class="table">
                        <tbody>

                        <?php foreach($purchases as $purchase): ?>
                            <tr>
                                <td>#<?= $purchase->id ?></td>

                                <td>
                                    <?= Html::encode(
                                        $purchase->product?->name
                                        ?: Yii::t('ui', 'Товар')
                                    ) ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        (float)$purchase->price,
                                        2,
                                        ',',
                                        ' '
                                    ) ?>
                                </td>

                                <td>
                                    <?= Html::encode($purchase->status) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        </tbody>
                    </table>

                <?php endif; ?>

            </div>

        </section>
    </div>
</div>
