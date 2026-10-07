<?php

use common\services\RecaptchaService;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Регистрация');

$captchaEnabled = $recaptcha->isEnabledFor(
    RecaptchaService::SCOPE_REGISTER
);
$captchaReady = $captchaEnabled && $recaptcha->isConfigured();
?>
<div class="container">
    <div class="auth-wrap">
        <div class="auth-panel">
            <div class="eyebrow"><?= Yii::t('app', 'Новый игрок') ?></div>
            <h1 class="h3 mb-4"><?= Yii::t('app', 'Создать HiveTech ID') ?></h1>

            <?php $form = ActiveForm::begin([
                'id' => 'register-form',
            ]); ?>

            <?= $form->field($model, 'username')
                ->textInput(['autocomplete' => 'username'])
                ->label(Yii::t('app', 'Игровой логин')) ?>

            <?= $form->field($model, 'email')
                ->input('email', ['autocomplete' => 'email'])
                ->label('Email') ?>

            <?= $form->field($model, 'password')
                ->passwordInput(['autocomplete' => 'new-password'])
                ->label(Yii::t('app', 'Пароль')) ?>

            <?= $form->field($model, 'passwordRepeat')
                ->passwordInput(['autocomplete' => 'new-password'])
                ->label(Yii::t('app', 'Повторите пароль')) ?>

            <?= $form->field($model, 'agree')
                ->checkbox()
                ->label(Yii::t('app', 'Я принимаю правила проекта')) ?>

            <?php if($captchaReady): ?>
                <?= Html::hiddenInput(
                    'g-recaptcha-response',
                    '',
                    ['id' => 'register-recaptcha-token']
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
                Yii::t('app', 'Создать аккаунт'),
                ['class' => 'btn btn-primary w-100']
            ) ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
if($captchaReady)
{
    $siteKey = Json::htmlEncode($recaptcha->getSiteKey());
    $action = Json::htmlEncode(
        RecaptchaService::SCOPE_REGISTER
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
    const form = $('#register-form');
    const tokenInput = $('#register-recaptcha-token');
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
