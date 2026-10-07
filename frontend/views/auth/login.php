<?php

use common\services\RecaptchaService;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Вход');

$captchaEnabled = $recaptcha->isEnabledFor(
    RecaptchaService::SCOPE_LOGIN
);
$captchaReady = $captchaEnabled && $recaptcha->isConfigured();
?>
<div class="container">
    <div class="auth-wrap">
        <div class="auth-panel">
            <div class="eyebrow">HiveTech ID</div>
            <h1 class="h3 mb-4"><?= Yii::t('app', 'Вход в аккаунт') ?></h1>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
            ]); ?>

            <?= $form->field($model, 'login')
                ->textInput([
                    'autocomplete' => 'username',
                    'placeholder' => Yii::t(
                        'app',
                        'Логин или email'
                    ),
                ])
                ->label(Yii::t('app', 'Логин или email')) ?>

            <?= $form->field($model, 'password')
                ->passwordInput(['autocomplete' => 'current-password'])
                ->label(Yii::t('app', 'Пароль')) ?>

            <?= $form->field($model, 'rememberMe')
                ->checkbox()
                ->label(Yii::t('app', 'Запомнить меня')) ?>

            <?php if($captchaReady): ?>
                <?= Html::hiddenInput(
                    'g-recaptcha-response',
                    '',
                    ['id' => 'login-recaptcha-token']
                ) ?>
            <?php elseif($captchaEnabled): ?>
                <div class="alert alert-warning">
                    <?= Yii::t(
                        'app',
                        'Защита от ботов временно недоступна.'
                    ) ?>
                </div>
            <?php endif; ?>

            <?= Html::submitButton(
                Yii::t('app', 'Войти'),
                ['class' => 'btn btn-primary w-100']
            ) ?>

            <?php ActiveForm::end(); ?>

            <div class="d-flex justify-content-between mt-3 small">
                <a href="/register"><?= Yii::t('app', 'Регистрация') ?></a>
                <a href="/resend-verification"><?= Yii::t('app', 'Повторить email') ?></a>
                <a href="/forgot-password"><?= Yii::t('app', 'Забыли пароль?') ?></a>
            </div>
        </div>
    </div>
</div>
<?php
if($captchaReady)
{
    $siteKey = Json::htmlEncode($recaptcha->getSiteKey());
    $action = Json::htmlEncode(
        RecaptchaService::SCOPE_LOGIN
    );

    $this->registerJsFile(
        'https://www.google.com/recaptcha/api.js?render='
            .rawurlencode($recaptcha->getSiteKey())
            .'&hl='
            .rawurlencode(Yii::$app->language),
        [
            'position' => View::POS_HEAD,
            'async' => true,
            'defer' => true,
        ]
    );

    $this->registerJs(<<<JS
(function () {
    const form = $('#login-form');
    const tokenInput = $('#login-recaptcha-token');
    let tokenReady = false;
    let tokenPending = false;

    form.on('beforeSubmit', function () {
        if(tokenReady)
        {
            return true;
        }

        if(tokenPending)
        {
            return false;
        }

        tokenPending = true;

        if(typeof grecaptcha === 'undefined')
        {
            tokenReady = true;
            tokenPending = false;
            tokenInput.val('');
            form.submit();
            return false;
        }

        grecaptcha.ready(function () {
            grecaptcha.execute({$siteKey}, {action: {$action}})
                .then(function (token) {
                    tokenInput.val(token);
                    tokenReady = true;
                    tokenPending = false;
                    form.submit();
                })
                .catch(function () {
                    tokenReady = true;
                    tokenPending = false;
                    tokenInput.val('');
                    form.submit();
                });
        });

        return false;
    });
})();
JS, View::POS_END);
}
?>
