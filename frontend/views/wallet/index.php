<?php

use yii\helpers\Html;

$this->title = Yii::t('ui', 'Баланс');

?>

<div class="container ht-section">
    <div class="account-grid">

        <?= $this->render('../account/_nav') ?>

        <section>

            <div class="row g-3">

                <div class="col-md-5">
                    <div class="ht-card">

                        <div class="muted">
                            <?= Yii::t('ui', 'Текущий баланс') ?>
                        </div>

                        <div class="metric">
                            <?= number_format(
                                (float)$wallet->balance,
                                2,
                                ',',
                                ' '
                            ) ?>
                        </div>

                        <hr>

                        <div class="alert alert-warning mb-0">
                            <strong>
                                <?= Yii::t(
                                    'ui',
                                    'Пополнение временно недоступно'
                                ) ?>
                            </strong>

                            <div class="mt-2">
                                <?= Yii::t(
                                    'ui',
                                    'Платёжная система находится в разработке. Баланс можно получать игровыми способами и за активность на проекте.'
                                ) ?>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-md-7">
                    <div class="ht-card">

                        <h3>
                            <?= Yii::t('ui', 'Операции') ?>
                        </h3>

                        <?php if(!$transactions): ?>

                            <p class="muted">
                                <?= Yii::t(
                                    'ui',
                                    'Операций пока нет.'
                                ) ?>
                            </p>

                        <?php else: ?>

                            <table class="table">
                                <tbody>

                                <?php foreach($transactions as $transaction): ?>
                                    <tr>

                                        <td>
                                            <?= Html::encode(
                                                $transaction->created_at
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= Html::encode(
                                                $transaction->description
                                            ) ?>
                                        </td>

                                        <td class="text-end">
                                            <?= $transaction->amount > 0
                                                ? '+'
                                                : ''
                                            ?>

                                            <?= number_format(
                                                (float)$transaction->amount,
                                                2,
                                                ',',
                                                ' '
                                            ) ?>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>

                                </tbody>
                            </table>

                        <?php endif; ?>

                    </div>
                </div>

            </div>

        </section>

    </div>
</div>
