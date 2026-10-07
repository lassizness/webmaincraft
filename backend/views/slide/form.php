<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord
    ? 'Новый слайд'
    : 'Редактирование слайда';
?>

<div class="admin-section-head">
    <div>
        <?= Html::a(
            '← К слайдеру',
            ['index'],
            ['class' => 'small text-decoration-none']
        ) ?>
        <div class="admin-panel-subtitle mt-2">
            Текст, изображение и действие промо-слайда.
        </div>
    </div>
</div>

<section class="admin-panel">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->label('Заголовок') ?>
    <?= $form->field($model, 'subtitle')
        ->textarea(['rows' => 3])
        ->label('Подзаголовок') ?>
    <?= $form->field($model, 'image_url')->label('URL изображения') ?>

    <div class="row g-3">
        <div class="col-md-5">
            <?= $form->field($model, 'button_text')
                ->label('Текст кнопки') ?>
        </div>
        <div class="col-md-7">
            <?= $form->field($model, 'button_url')
                ->label('Ссылка кнопки') ?>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <?= $form->field($model, 'enabled')
                ->checkbox()
                ->label('Включён') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'sort')
                ->input('number')
                ->label('Сортировка') ?>
        </div>
    </div>

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
