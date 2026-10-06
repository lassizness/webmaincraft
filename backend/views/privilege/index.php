<?php

use common\models\UserPrivilege;
use yii\grid\GridView;
use yii\helpers\{
    ArrayHelper,
    Html,
    Url
};

$this->title =
    'Игровые привилегии';

?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="h3 mb-1">
            Игровые привилегии
        </h1>
        <div class="text-secondary">
            FTBUtilities ranks, сроки и синхронизация через RCON.
        </div>
    </div>

    <div>
        <a
            class="btn btn-outline-light"
            href="<?= Url::to(
                [
                    'history',
                ]
            ) ?>"
        >
            История
        </a>

        <a
            class="btn btn-primary"
            href="<?= Url::to(
                [
                    'group',
                ]
            ) ?>"
        >
            Новый статус
        </a>
    </div>

</div>


<div class="row g-3 mb-4">

    <?php foreach($groups as $group): ?>

        <div class="col-md-6 col-xl-3">

            <div class="card p-3 h-100">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="small text-secondary">
                            <?= Html::encode(
                                $group->code
                            ) ?>
                        </div>

                        <h4
                            class="mb-1"
                            style="color: <?= Html::encode(
                                $group->color ?: '#ffffff'
                            ) ?>"
                        >
                            <?= Html::encode(
                                $group->name
                            ) ?>
                        </h4>

                    </div>

                    <span class="badge <?= $group->enabled
                        ? 'bg-success'
                        : 'bg-secondary'
                    ?>">
                        <?= $group->enabled
                            ? 'Включён'
                            : 'Выключен'
                        ?>
                    </span>

                </div>

                <div class="small mt-2">
                    FTBU:
                    <code><?= Html::encode(
                        $group->ftbu_rank
                    ) ?></code>
                </div>

                <div class="small text-secondary">
                    Уровень:
                    <?= (int)$group->level ?>
                </div>

                <div class="mt-3">
                    <a
                        class="btn btn-sm btn-outline-light"
                        href="<?= Url::to(
                            [
                                'group',
                                'id' =>
                                    $group->id,
                            ]
                        ) ?>"
                    >
                        Изменить
                    </a>
                </div>

            </div>

        </div>

    <?php endforeach; ?>

</div>


<div class="card p-4 mb-4">

    <h4 class="mb-3">
        Выдать статус вручную
    </h4>

    <?= Html::beginForm(
        [
            '/privilege/grant',
        ],
        'post'
    ) ?>

    <div class="row g-3 align-items-end">

        <div class="col-md-3">

            <label class="form-label">
                Игровой логин
            </label>

            <input
                class="form-control"
                name="username"
                required
                maxlength="16"
                placeholder="testsql"
            >

        </div>

        <div class="col-md-3">

            <label class="form-label">
                Статус
            </label>

            <?= Html::dropDownList(
                'group_id',
                null,
                ArrayHelper::map(
                    $groups,
                    'id',
                    'name'
                ),
                [
                    'class' =>
                        'form-select',
                    'required' =>
                        true,
                ]
            ) ?>

        </div>

        <div class="col-md-3">

            <label class="form-label">
                Сервер
            </label>

            <?= Html::dropDownList(
                'server_id',
                null,
                ArrayHelper::map(
                    $servers,
                    'id',
                    'name'
                ),
                [
                    'class' =>
                        'form-select',
                    'required' =>
                        true,
                ]
            ) ?>

        </div>

        <div class="col-md-2">

            <label class="form-label">
                Дней
            </label>

            <input
                type="number"
                class="form-control"
                name="days"
                value="30"
                min="1"
                max="3650"
                required
            >

        </div>

        <div class="col-md-1">

            <button class="btn btn-primary w-100">
                Выдать
            </button>

        </div>

    </div>

    <?= Html::endForm() ?>

</div>


<div class="card p-4">

    <h4 class="mb-3">
        Выданные статусы
    </h4>

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
                    [
                        'label' =>
                            'Статус',
                        'format' =>
                            'raw',
                        'value' =>
                            static function($model)
                            {
                                $name =
                                    Html::encode(
                                        $model->group?->name
                                        ?: '#'
                                        . $model->privilege_group_id
                                    );

                                $color =
                                    Html::encode(
                                        $model->group?->color
                                        ?: '#ffffff'
                                    );

                                return '<strong style="color:'
                                    . $color
                                    . '">'
                                    . $name
                                    . '</strong>';
                            },
                    ],
                    'status',
                    'source',
                    'active_from',
                    'active_to',
                    [
                        'format' =>
                            'raw',
                        'value' =>
                            static function($model)
                            {
                                if(
                                    $model->status
                                    !== UserPrivilege::STATUS_ACTIVE
                                )
                                {
                                    return '';
                                }

                                return Html::beginForm(
                                    [
                                        '/privilege/revoke',
                                        'id' =>
                                            $model->id,
                                    ],
                                    'post',
                                    [
                                        'class' =>
                                            'd-inline',
                                    ]
                                )
                                . Html::submitButton(
                                    'Отозвать',
                                    [
                                        'class' =>
                                            'btn btn-sm btn-outline-danger',
                                        'data-confirm' =>
                                            'Отозвать статус?',
                                    ]
                                )
                                . Html::endForm();
                            },
                    ],
                ],
        ]
    ) ?>

</div>
