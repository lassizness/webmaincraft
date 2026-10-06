<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord
    ? 'Новая категория'
    : 'Категория магазина';

?>

<div class="card p-4">

    <h1 class="h4 mb-4">
        <?= Html::encode($this->title) ?>
    </h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form
        ->field($model, 'name')
        ->label('Название') ?>

    <?= $form
        ->field($model, 'slug')
        ->label('Slug') ?>

    <?= $form
        ->field($model, 'description')
        ->textarea([
            'rows' => 4,
        ])
        ->label('Описание') ?>

    <?= $form
        ->field($model, 'enabled')
        ->checkbox()
        ->label('Активна') ?>

    <?= $form
        ->field($model, 'sort')
        ->input('number')
        ->label('Сортировка') ?>

    <?= Html::submitButton(
        'Сохранить',
        ['class' => 'btn btn-primary']
    ) ?>

    <?php ActiveForm::end(); ?>

</div>
