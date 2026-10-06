<?php

use common\models\{
    PrivilegeGroup,
    Server,
    ShopCategory,
    ShopProduct
};
use yii\helpers\{
    ArrayHelper,
    Html
};
use yii\widgets\ActiveForm;

$this->title =
    'Товар';

?>

<div class="card p-4">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form
        ->field(
            $model,
            'category_id'
        )
        ->dropDownList(
            ArrayHelper::map(
                ShopCategory::find()
                    ->orderBy(
                        'sort'
                    )
                    ->all(),
                'id',
                'name'
            ),
            [
                'prompt' =>
                    'Без категории',
            ]
        )
        ->label(
            'Категория'
        ) ?>

    <?= $form
        ->field(
            $model,
            'server_id'
        )
        ->dropDownList(
            ArrayHelper::map(
                Server::find()
                    ->orderBy(
                        'sort'
                    )
                    ->all(),
                'id',
                'name'
            ),
            [
                'prompt' =>
                    'Любой / без сервера',
            ]
        )
        ->hint(
            'Для товара типа «Статус» сервер обязателен.'
        )
        ->label(
            'Сервер'
        ) ?>

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
            'slug'
        )
        ->label(
            'Slug'
        ) ?>

    <?= $form
        ->field(
            $model,
            'description'
        )
        ->textarea()
        ->label(
            'Описание'
        ) ?>

    <?= $form
        ->field(
            $model,
            'price'
        )
        ->input(
            'number',
            [
                'step' =>
                    '0.01',
            ]
        )
        ->label(
            'Цена'
        ) ?>

    <hr class="my-4">

    <?= $form
        ->field(
            $model,
            'product_type'
        )
        ->dropDownList(
            [
                ShopProduct::TYPE_ITEM =>
                    'Обычный товар',
                ShopProduct::TYPE_PRIVILEGE =>
                    'Игровой статус',
            ]
        )
        ->label(
            'Тип товара'
        ) ?>

    <?= $form
        ->field(
            $model,
            'privilege_group_id'
        )
        ->dropDownList(
            ArrayHelper::map(
                PrivilegeGroup::find()
                    ->where(
                        [
                            'enabled' => 1,
                        ]
                    )
                    ->orderBy(
                        [
                            'sort' =>
                                SORT_ASC,
                        ]
                    )
                    ->all(),
                'id',
                'name'
            ),
            [
                'prompt' =>
                    'Не статус',
            ]
        )
        ->hint(
            'Используется только для типа «Игровой статус».'
        )
        ->label(
            'Статус'
        ) ?>

    <?= $form
        ->field(
            $model,
            'duration_days'
        )
        ->input(
            'number',
            [
                'min' =>
                    1,
                'max' =>
                    3650,
                'placeholder' =>
                    '30',
            ]
        )
        ->hint(
            'Срок действия статуса в днях.'
        )
        ->label(
            'Срок, дней'
        ) ?>

    <hr class="my-4">

    <?= $form
        ->field(
            $model,
            'delivery_type'
        )
        ->dropDownList(
            [
                'none' =>
                    'none',
                'rcon_command' =>
                    'RCON command',
                'permission' =>
                    'permission (RCON)',
                'cosmetic' =>
                    'cosmetic (RCON)',
            ]
        )
        ->hint(
            'Для статусов не используется: команда ranks формируется автоматически.'
        )
        ->label(
            'Обычная доставка'
        ) ?>

    <?= $form
        ->field(
            $model,
            'delivery_payload'
        )
        ->textarea(
            [
                'rows' => 4,
            ]
        )
        ->hint(
            'Для обычных товаров поддерживаются {username} и {uuid}.'
        )
        ->label(
            'Payload'
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

    <?= Html::submitButton(
        'Сохранить',
        [
            'class' =>
                'btn btn-primary',
        ]
    ) ?>

    <?php ActiveForm::end(); ?>

</div>
