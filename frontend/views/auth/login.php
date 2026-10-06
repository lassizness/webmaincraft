<?php

use common\services\RecaptchaService;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

$this->title = 'Вход';

$captchaEnabled = $recaptcha->isEnabledFor(RecaptchaService::SCOPE_LOGIN);
$captchaReady = $captchaEnabled && $recaptcha->isConfigured();
?>
<div class="container">
    <div class="auth-wrap">
        <div class="auth-panel">
            <div class="eyebrow">HiveTech ID</div>
            <h1 class="h3 mb-4">Вход в аккаунт</h1>

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'login')
                ->textInput(['autocomplete' => 'username', 'placeholder' => 'Логин или email'])
                ->label('Логин или email') ?>

            <?= $form->field($model, 'password')
                ->passwordInput(['autocomplete' => 'current-password'])
                ->label('Пароль') ?>

            <?= $form->field($model, 'rememberMe')->checkbox()->label('Запомнить меня') ?>

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

            <?= Html::submitButton('Войти', ['class' => 'btn btn-primary w-100']) ?>

            <?php ActiveForm::end(); ?>

            <div class="d-flex justify-content-between mt-3 small">
                <a href="/register">Регистрация</a>
                <a href="/resend-verification">Повторить email</a>
                <a href="/forgot-password">Забыли пароль?</a>
            </div>
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
