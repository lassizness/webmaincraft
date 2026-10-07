<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Повторить подтверждение email');
?>
<div class="container">
    <div class="auth-wrap">
        <div class="auth-panel">
            <h1 class="h3"><?= Yii::t('app', 'Повторить письмо') ?></h1>
            <p class="muted"><?= Yii::t('app', 'Если email ещё не подтверждён, мы создадим новую ссылку и отправим её повторно.') ?></p>
            <?php $form = ActiveForm::begin(); ?>
            <?= $form->field($model, 'email')->input('email')->label('Email') ?>
            <?= Html::submitButton(Yii::t('app', 'Отправить'), ['class' => 'btn btn-primary']) ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
