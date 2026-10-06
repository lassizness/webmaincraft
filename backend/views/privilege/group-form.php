<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title =
    $model->isNewRecord
        ? 'Новый статус'
        : 'Редактирование статуса';

?>

<div class="card p-4">

    <h1 class="h4 mb-4">
        <?= Html::encode(
            $this->title
        ) ?>
    </h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form
        ->field(
            $model,
            'name'
        )
        ->label(
            'Название'
        ) ?>

    <?= $form
        ->field(
            $model,
            'code'
        )
        ->textInput(
            [
                'placeholder' =>
                    'vip',
            ]
        )
        ->label(
            'Код'
        ) ?>

    <?= $form
        ->field(
            $model,
            'ftbu_rank'
        )
        ->textInput(
            [
                'placeholder' =>
                    'vip',
            ]
        )
        ->hint(
            'Должен совпадать с rank в local/ftbutilities/ranks.txt'
        )
        ->label(
            'FTBUtilities rank'
        ) ?>

    <?= $form
        ->field(
            $model,
            'description'
        )
        ->textarea(
            [
                'rows' => 4,
            ]
        )
        ->label(
            'Описание'
        ) ?>

    <?= $form
        ->field(
            $model,
            'color'
        )
        ->textInput(
            [
                'placeholder' =>
                    '#39d98a',
            ]
        )
        ->label(
            'Цвет'
        ) ?>

    <?= $form
        ->field(
            $model,
            'level'
        )
        ->input(
            'number',
            [
                'min' => 1,
            ]
        )
        ->hint(
            'Чем больше число, тем выше статус. Используется для проверки upgrade/downgrade.'
        )
        ->label(
            'Уровень'
        ) ?>

    <?= $form
        ->field(
            $model,
            'sort'
        )
        ->input(
            'number'
        )
        ->label(
            'Сортировка'
        ) ?>

    <?= $form
        ->field(
            $model,
            'enabled'
        )
        ->checkbox()
        ->label(
            'Активен'
        ) ?>

    <?= Html::submitButton(
        'Сохранить',
        [
            'class' =>
                'btn btn-primary',
        ]
    ) ?>

    <?php ActiveForm::end(); ?>

</div>
