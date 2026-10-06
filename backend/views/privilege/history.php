<?php

use yii\grid\GridView;
use yii\helpers\{
    Html,
    Url
};

$this->title =
    'История привилегий';

?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <h1 class="h3 mb-0">
        История привилегий
    </h1>

    <a
        class="btn btn-outline-light"
        href="<?= Url::to(
            [
                'index',
            ]
        ) ?>"
    >
        Назад
    </a>

</div>

<div class="card p-4">

    <?= GridView::widget(
        [
            'dataProvider' =>
                $dataProvider,
            'columns' =>
                [
                    'id',
                    [
                        'label' =>
                            'Игрок',
                        'value' =>
                            static fn($model) =>
                                $model->user?->username
                                ?: '#'
                                . $model->user_id,
                    ],
                    [
                        'label' =>
                            'Сервер',
                        'value' =>
                            static fn($model) =>
                                $model->server?->name
                                ?: '#'
                                . $model->server_id,
                    ],
                    'action',
                    [
                        'label' =>
                            'Было',
                        'value' =>
                            static fn($model) =>
                                $model->fromGroup?->name
                                ?: '-',
                    ],
                    [
                        'label' =>
                            'Стало',
                        'value' =>
                            static fn($model) =>
                                $model->toGroup?->name
                                ?: '-',
                    ],
                    'old_active_to',
                    'new_active_to',
                    'source',
                    'created_at',
                ],
        ]
    ) ?>

</div>
