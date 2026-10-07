<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord
    ? 'Новая страница'
    : 'Редактирование страницы';
?>

<div class="admin-section-head">
    <div>
        <?= Html::a(
            '← К страницам',
            ['index'],
            ['class' => 'small text-decoration-none']
        ) ?>
        <div class="admin-panel-subtitle mt-2">
            Контент, SEO-поля и порядок отображения страницы.
        </div>
    </div>
</div>

<section class="admin-panel">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->label('Заголовок') ?>
    <?= $form->field($model, 'slug')
        ->hint('Используется в URL /page/{slug}.') ?>

    <?= $form->field($model, 'body')
        ->textarea(['rows' => 16])
        ->label('Содержимое') ?>

    <hr class="my-4">

    <h3 class="admin-panel-title mb-3">SEO</h3>
    <?= $form->field($model, 'meta_title')->label('Meta title') ?>
    <?= $form->field($model, 'meta_description')
        ->textarea(['rows' => 3])
        ->label('Meta description') ?>

    <div class="row g-3">
        <div class="col-md-6">
            <?= $form->field($model, 'published')
                ->checkbox()
                ->label('Опубликована') ?>
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
