<?php

use common\services\RecaptchaService;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

$this->title = 'Регистрация';

$captchaEnabled = $recaptcha->isEnabledFor(RecaptchaService::SCOPE_REGISTER);
$captchaReady = $captchaEnabled && $recaptcha->isConfigured();
?>
<div class="container">
    <div class="auth-wrap">
        <div class="auth-panel">
            <div class="eyebrow">Новый игрок</div>
            <h1 class="h3 mb-4">Создать HiveTech ID</h1>

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'username')
                ->textInput(['autocomplete' => 'username'])
                ->label('Игровой логин') ?>

            <?= $form->field($model, 'email')
                ->input('email', ['autocomplete' => 'email'])
                ->label('Email') ?>

            <?= $form->field($model, 'password')
                ->passwordInput(['autocomplete' => 'new-password'])
                ->label('Пароль') ?>

            <?= $form->field($model, 'passwordRepeat')
                ->passwordInput(['autocomplete' => 'new-password'])
                ->label('Повторите пароль') ?>

            <?= $form->field($model, 'agree')->checkbox()->label('Я принимаю правила проекта') ?>

            <?php if($captchaReady): ?>
                <div class="mb-3">
                    <div
                        class="g-recaptcha"
                        data-sitekey="<?= Html::encode($recaptcha->getSiteKey()) ?>"
                        data-theme="dark"
                    ></div>
                </div>
            <?php elseif($captchaEnabled): ?>
                <div class="alert alert-warning">Защита от ботов временно недоступна.</div>
            <?php endif; ?>

            <?= Html::submitButton('Создать аккаунт', ['class' => 'btn btn-primary w-100']) ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
if($captchaReady)
{
    $this->registerJsFile(
        'https://www.google.com/recaptcha/api.js?hl=ru',
        [
            'position' => View::POS_HEAD,
            'async' => true,
            'defer' => true,
        ]
    );
}
?>
