<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord
    ? 'Новая категория новостей'
    : 'Редактирование категории';
?>

<div class="admin-section-head">
    <div>
        <?= Html::a(
            '← К категориям',
            ['index'],
            ['class' => 'small text-decoration-none']
        ) ?>
    </div>
</div>

<section class="admin-panel">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->label('Название') ?>
    <?= $form->field($model, 'slug')
        ->hint('Используется как стабильный машинный идентификатор.') ?>

    <div class="admin-actions mt-2">
        <?= Html::submitButton(
            'Сохранить',
            ['class' => 'btn btn-primary']
        ) ?>
        <?= Html::a(
            'Отмена',
            ['index'],
            ['class' => 'btn btn-outline-secondary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>
</section>
