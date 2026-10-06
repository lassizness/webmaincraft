<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('ui', 'Безопасность');

?>

<div class="container ht-section">
    <div class="account-grid">

        <?= $this->render('_nav') ?>

        <section>
            <div class="ht-card">

                <h2>
                    <?= Yii::t('ui', 'Смена пароля') ?>
                </h2>

                <p class="muted">
                    <?= Yii::t(
                        'ui',
                        'После изменения будут сброшены web-сессии, accessToken и serverID лаунчера.'
                    ) ?>
                </p>

                <?php $form = ActiveForm::begin(); ?>

                <?= $form
                    ->field($model, 'currentPassword')
                    ->passwordInput()
                    ->label(Yii::t('ui', 'Текущий пароль')) ?>

                <?= $form
                    ->field($model, 'password')
                    ->passwordInput()
                    ->label(Yii::t('ui', 'Новый пароль')) ?>

                <?= $form
                    ->field($model, 'passwordRepeat')
                    ->passwordInput()
                    ->label(Yii::t('ui', 'Повторите пароль')) ?>

                <?= Html::submitButton(
                    Yii::t('ui', 'Изменить пароль'),
                    ['class' => 'btn btn-primary']
                ) ?>

                <?php ActiveForm::end(); ?>

            </div>
        </section>

    </div>
</div>
