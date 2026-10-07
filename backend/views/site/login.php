<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Вход в админ-панель';
$this->context->layout = 'main';
?>

<div class="admin-login-card">
    <div class="admin-login-accent"></div>

    <div class="admin-login-body">
        <div class="admin-login-brand">
            <span class="admin-brand-mark">H</span>
            <div>
                <h1 class="admin-login-title">HiveTech Admin</h1>
                <div class="admin-login-subtitle">
                    Управление игровым контуром HiveGrid
                </div>
            </div>
        </div>

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'login')
            ->textInput([
                'autocomplete' => 'username',
                'autofocus' => true,
                'placeholder' => 'Логин или email',
            ])
            ->label('Логин') ?>

        <?= $form->field($model, 'password')
            ->passwordInput([
                'autocomplete' => 'current-password',
                'placeholder' => 'Пароль',
            ])
            ->label('Пароль') ?>

        <?= $form->field($model, 'rememberMe')
            ->checkbox()
            ->label('Запомнить меня') ?>

        <?= Html::submitButton(
            'Войти в панель',
            ['class' => 'btn btn-primary w-100 mt-2']
        ) ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
