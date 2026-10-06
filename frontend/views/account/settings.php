<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('ui', 'Профиль');

?>

<div class="container ht-section">
    <div class="account-grid">

        <?= $this->render('_nav') ?>

        <section>
            <div class="ht-card">

                <h2>
                    <?= Yii::t('ui', 'Профиль') ?>
                </h2>

                <p class="muted">
                    <?= Yii::t(
                        'ui',
                        'Смена email временно переводит аккаунт в pending до подтверждения. Это одновременно закрывает вход в лаунчер.'
                    ) ?>
                </p>

                <?php $form = ActiveForm::begin(); ?>

                <?= $form
                    ->field($model, 'email')
                    ->input('email')
                    ->label('Email') ?>

                <?= Html::submitButton(
                    Yii::t('ui', 'Сохранить'),
                    ['class' => 'btn btn-primary']
                ) ?>

                <?php ActiveForm::end(); ?>

            </div>
        </section>

    </div>
</div>
