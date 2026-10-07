<?php

use common\models\NewsCategory;
use yii\helpers\{ArrayHelper, Html};
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord
    ? 'Новая новость'
    : 'Редактирование новости';
?>

<div class="admin-section-head">
    <div>
        <?= Html::a(
            '← К новостям',
            ['index'],
            ['class' => 'small text-decoration-none']
        ) ?>
        <div class="admin-panel-subtitle mt-2">
            Заголовок, публикация и содержимое новости.
        </div>
    </div>
</div>

<section class="admin-panel">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->label('Заголовок') ?>

    <?= $form->field($model, 'slug')
        ->hint('Используется в URL новости.') ?>

    <?= $form->field($model, 'category_id')
        ->dropDownList(
            ArrayHelper::map(
                NewsCategory::find()->orderBy('name')->all(),
                'id',
                'name'
            ),
            ['prompt' => 'Без категории']
        )
        ->label('Категория') ?>

    <?= $form->field($model, 'excerpt')
        ->textarea(['rows' => 3])
        ->label('Краткое описание') ?>

    <?= $form->field($model, 'body')
        ->textarea(['rows' => 14])
        ->label('Текст новости') ?>

    <div class="row g-3">
        <div class="col-md-6">
            <?= $form->field($model, 'published')
                ->checkbox()
                ->label('Опубликована') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'published_at')
                ->textInput(['placeholder' => 'YYYY-MM-DD HH:MM:SS'])
                ->label('Дата публикации') ?>
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
