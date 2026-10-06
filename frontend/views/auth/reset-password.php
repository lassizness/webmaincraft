<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Новый пароль');
?>
<div class="container">
    <div class="auth-wrap">
        <div class="auth-panel">
            <h1 class="h3"><?= Yii::t('app', 'Новый пароль') ?></h1>
            <?php $form = ActiveForm::begin(); ?>
            <?= $form->field($model, 'password')->passwordInput()->label(Yii::t('app', 'Новый пароль')) ?>
            <?= $form->field($model, 'passwordRepeat')->passwordInput()->label(Yii::t('app', 'Повторите пароль')) ?>
            <?= Html::submitButton(Yii::t('app', 'Изменить пароль'), ['class' => 'btn btn-primary']) ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
