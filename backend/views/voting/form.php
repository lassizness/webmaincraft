<?php

use common\models\VoteService;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord ? 'Новый сервис голосования' : 'Сервис голосования';
$appUrl = rtrim((string)(Yii::$app->params['appUrl'] ?? ''), '/');
$callbackUrl = $appUrl !== '' && !$model->isNewRecord && $model->code
    ? $appUrl . '/vote/' . $model->code . '/callback'
    : null;
?>

<div class="card p-4">
    <?php if ($callbackUrl !== null): ?>
        <div class="alert alert-info">
            <strong>Callback URL:</strong>
            <code><?= Html::encode($callbackUrl) ?></code>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'code')
        ->textInput(['maxlength' => true, 'placeholder' => 'topcraft'])
        ->hint('Используется в URL /vote/{code}. После запуска сервиса лучше не менять.') ?>

    <?= $form->field($model, 'driver')
        ->dropDownList(VoteService::driverOptions())
        ->hint('TopG и TopCraft используют разные форматы callback, но общую историю голосов и наград.') ?>

    <?= $form->field($model, 'vote_url_template')
        ->textInput(['maxlength' => true, 'placeholder' => 'https://topcraft.club/servers/13035/'])
        ->hint('Для TopG обязателен {username}. Для TopCraft можно указать обычную страницу проекта.') ?>

    <?= $form->field($model, 'reward_amount')
        ->input('number', ['step' => '0.01', 'min' => '0.01'])
        ->hint('Сколько кварков начислять за подтверждённый голос этого сервиса.') ?>

    <?= $form->field($model, 'reward_cooldown_seconds')
        ->input('number', ['min' => 60, 'step' => 60])
        ->hint('86400 = 24 часа; 43200 = 12 часов. Кулдаун считается отдельно для каждого рейтинга.') ?>

    <?= $form->field($model, 'callback_secret')
        ->textInput(['maxlength' => true, 'autocomplete' => 'off'])
        ->hint('Для TopCraft вставь «Секретный ключ» из раздела «Скрипт поощрений». Для TopG оставь пустым.') ?>

    <?= $form->field($model, 'banner_url')
        ->textInput(['maxlength' => true, 'placeholder' => 'https://topcraft.club/media/projects/13035/tops.png'])
        ->hint('Необязательно. Если заполнить и включить показ на главной, баннер рейтинга появится на сайте автоматически.') ?>

    <?= $form->field($model, 'show_on_home')->checkbox() ?>

    <?= $form->field($model, 'config')
        ->textarea(['rows' => 6, 'class' => 'form-control font-monospace'])
        ->hint('Дополнительные параметры драйвера в JSON. Для TopG/TopCraft обычно можно оставить пустым.') ?>

    <?= $form->field($model, 'enabled')->checkbox() ?>
    <?= $form->field($model, 'sort')->input('number') ?>

    <div class="d-flex gap-2">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
