<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord
    ? 'Новый счётчик'
    : 'Счётчик: ' . $model->name;
?>

<div class="card p-4">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput([
        'maxlength' => true,
        'placeholder' => 'Например: Яндекс.Метрика',
    ]) ?>

    <div class="alert alert-warning">
        Код отсюда выводится на публичном сайте без HTML-экранирования.
        Добавляйте только доверенный код аналитики.
    </div>

    <?= $form->field($model, 'head_code')->textarea([
        'rows' => 10,
        'class' => 'form-control font-monospace',
        'placeholder' => '<script>...</script>',
    ])->hint('Вставляется перед закрывающим тегом </head>.') ?>

    <?= $form->field($model, 'body_start_code')->textarea([
        'rows' => 10,
        'class' => 'form-control font-monospace',
        'placeholder' => '<noscript>...</noscript>',
    ])->hint('Вставляется сразу после открытия <body>.') ?>

    <?= $form->field($model, 'body_end_code')->textarea([
        'rows' => 10,
        'class' => 'form-control font-monospace',
        'placeholder' => '<script>...</script>',
    ])->hint('Вставляется перед закрывающим тегом </body>.') ?>

    <div class="row">

        <div class="col-md-6">
            <?= $form->field($model, 'enabled')->checkbox() ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'sort')->input('number') ?>
        </div>

    </div>

    <?= Html::submitButton(
        'Сохранить',
        ['class' => 'btn btn-primary']
    ) ?>

    <?= Html::a(
        'Отмена',
        ['index'],
        ['class' => 'btn btn-outline-secondary ms-2']
    ) ?>

    <?php ActiveForm::end(); ?>

</div>
