<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Восстановление пароля');
?>
<div class="container">
    <div class="auth-wrap">
        <div class="auth-panel">
            <h1 class="h3"><?= Yii::t('app', 'Восстановление пароля') ?></h1>
            <p class="muted"><?= Yii::t('app', 'Ссылка действует один час.') ?></p>
            <?php $form = ActiveForm::begin(); ?>
            <?= $form->field($model, 'email')->input('email')->label('Email') ?>
            <?= Html::submitButton(Yii::t('app', 'Отправить ссылку'), ['class' => 'btn btn-primary']) ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
