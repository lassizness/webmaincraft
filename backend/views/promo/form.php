<?php

use common\models\ShopProduct;
use yii\helpers\{ArrayHelper, Html};
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord
    ? 'Новый промокод'
    : 'Редактирование промокода';
?>

<div class="admin-section-head">
    <div>
        <?= Html::a(
            '← К промокодам',
            ['index'],
            ['class' => 'small text-decoration-none']
        ) ?>
        <div class="admin-panel-subtitle mt-2">
            Настройка награды, лимита и срока действия.
        </div>
    </div>
</div>

<section class="admin-panel">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'code')->label('Код') ?>

    <div class="row g-3">
        <div class="col-md-6">
            <?= $form->field($model, 'type')
                ->dropDownList([
                    'wallet' => 'Баланс',
                    'product' => 'Товар',
                ])
                ->label('Тип награды') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'value')
                ->input('number', ['step' => '0.01'])
                ->label('Значение') ?>
        </div>
    </div>

    <?= $form->field($model, 'product_id')
        ->dropDownList(
            ArrayHelper::map(
                ShopProduct::find()->orderBy('name')->all(),
                'id',
                'name'
            ),
            ['prompt' => 'Нет']
        )
        ->label('Товар') ?>

    <div class="row g-3">
        <div class="col-md-6">
            <?= $form->field($model, 'max_uses')
                ->input('number')
                ->label('Максимум использований') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'expires_at')
                ->textInput(['placeholder' => 'YYYY-MM-DD HH:MM:SS'])
                ->label('Истекает') ?>
        </div>
    </div>

    <?= $form->field($model, 'enabled')
        ->checkbox()
        ->label('Активен') ?>

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
