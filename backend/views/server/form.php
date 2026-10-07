<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord
    ? 'Новый сервер'
    : 'Редактирование сервера';
?>

<div class="admin-section-head">
    <div>
        <?= Html::a(
            '← К серверам',
            ['index'],
            ['class' => 'small text-decoration-none']
        ) ?>
        <div class="admin-panel-subtitle mt-2">
            Сетевые параметры и RCON-привязка игрового сервера.
        </div>
    </div>
</div>

<section class="admin-panel">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row g-3">
        <div class="col-md-7">
            <?= $form->field($model, 'name')->label('Название') ?>
        </div>
        <div class="col-md-5">
            <?= $form->field($model, 'code')->label('Код') ?>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <?= $form->field($model, 'host')->label('Host') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'port')
                ->input('number')
                ->label('Port') ?>
        </div>
    </div>

    <?= $form->field($model, 'description')
        ->textarea(['rows' => 3])
        ->label('Описание') ?>

    <?= $form->field($model, 'rcon_env_key')
        ->hint('Например MAIN → MINECRAFT_RCON_MAIN_PASSWORD в env')
        ->label('RCON env key') ?>

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
