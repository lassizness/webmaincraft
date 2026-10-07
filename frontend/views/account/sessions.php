<?php

use yii\helpers\Html;

$this->title = Yii::t('ui', 'Сессии');

?>

<div class="container ht-section">
    <div class="account-grid">

        <?= $this->render('_nav') ?>

        <section>
            <div class="ht-card">

                <h2>
                    <?= Yii::t('ui', 'Активность входов') ?>
                </h2>

                <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>IP</th>
                        <th><?= Yii::t('ui', 'Устройство') ?></th>
                        <th><?= Yii::t('ui', 'Последняя активность') ?></th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php foreach($sessions as $session): ?>

                        <tr>
                            <td>
                                <?= Html::encode($session->ip_address) ?>
                            </td>

                            <td>
                                <?= Html::encode(
                                    mb_substr(
                                        $session->user_agent,
                                        0,
                                        65
                                    )
                                ) ?>
                            </td>

                            <td>
                                <?= Html::encode($session->last_seen_at) ?>

                                <?php if($session->revoked_at): ?>
                                    · <?= Yii::t('ui', 'завершена') ?>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if(!$session->revoked_at): ?>

                                    <?= Html::beginForm(
                                        [
                                            '/account/revoke-session',
                                            'id' => $session->id,
                                        ],
                                        'post'
                                    ) ?>

                                    <?= Html::submitButton(
                                        Yii::t('ui', 'Завершить'),
                                        [
                                            'class' =>
                                                'btn btn-sm btn-outline-danger',
                                        ]
                                    ) ?>

                                    <?= Html::endForm() ?>

                                <?php endif; ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>
                </table>
                </div>

            </div>
        </section>

    </div>
</div>
