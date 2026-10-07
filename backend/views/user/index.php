<?php

use common\models\User;
use yii\grid\GridView;
use yii\helpers\{Html, Url};

$this->title = 'Пользователи';

$statusLabels = [
    User::STATUS_ACTIVE => 'Активен',
    User::STATUS_PENDING => 'Pending',
    User::STATUS_BLOCKED => 'Заблокирован',
];

$statusClasses = [
    User::STATUS_ACTIVE => 'bg-success',
    User::STATUS_PENDING => 'bg-warning',
    User::STATUS_BLOCKED => 'bg-danger',
];
?>

<section class="admin-panel mb-3">
    <div class="admin-section-head mb-3">
        <div>
            <h2 class="admin-panel-title">Поиск и фильтры</h2>
            <div class="admin-panel-subtitle">
                Ищи по логину, email или точному UUID.
            </div>
        </div>
    </div>

    <?= Html::beginForm(['/user/index'], 'get', [
        'class' => 'admin-search-bar',
    ]) ?>

        <div>
            <label class="form-label" for="user-search">
                Пользователь
            </label>
            <input
                id="user-search"
                class="form-control"
                name="q"
                value="<?= Html::encode($q) ?>"
                placeholder="username / email / UUID"
                autocomplete="off"
            >
        </div>

        <div>
            <label class="form-label" for="user-status">
                Статус
            </label>
            <?= Html::dropDownList(
                'status',
                $status,
                ['' => 'Все статусы'] + $statusLabels,
                [
                    'id' => 'user-status',
                    'class' => 'form-select',
                ]
            ) ?>
        </div>

        <div>
            <label class="form-label" for="user-verified">
                Email
            </label>
            <?= Html::dropDownList(
                'verified',
                $verified,
                [
                    '' => 'Любой',
                    '1' => 'Подтверждён',
                    '0' => 'Не подтверждён',
                ],
                [
                    'id' => 'user-verified',
                    'class' => 'form-select',
                ]
            ) ?>
        </div>

        <div class="admin-search-actions d-flex gap-2">
            <button class="btn btn-primary" type="submit">
                Найти
            </button>
            <?= Html::a(
                'Сбросить',
                ['/user/index'],
                ['class' => 'btn btn-outline-secondary']
            ) ?>
        </div>

    <?= Html::endForm() ?>
</section>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => [
        'class' => 'table table-hover align-middle',
    ],
    'columns' => [
        [
            'attribute' => 'id',
            'contentOptions' => ['class' => 'text-secondary'],
        ],
        [
            'attribute' => 'username',
            'format' => 'raw',
            'value' => static fn(User $model): string => Html::a(
                Html::encode($model->username),
                ['/user/view', 'id' => $model->id],
                ['class' => 'fw-bold text-decoration-none']
            ),
        ],
        'email:email',
        [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => static fn(User $model): string => Html::tag(
                'span',
                Html::encode(
                    $statusLabels[$model->status] ?? $model->status
                ),
                [
                    'class' => 'badge '
                        .($statusClasses[$model->status] ?? 'bg-secondary'),
                ]
            ),
        ],
        [
            'attribute' => 'email_verified',
            'label' => 'Email',
            'format' => 'raw',
            'value' => static fn(User $model): string => Html::tag(
                'span',
                $model->email_verified
                    ? 'Подтверждён'
                    : 'Не подтверждён',
                [
                    'class' => 'badge '
                        .($model->email_verified
                            ? 'bg-success'
                            : 'bg-secondary'
                        ),
                ]
            ),
        ],
        [
            'attribute' => 'last_login_at',
            'label' => 'Последний вход',
            'value' => static fn(User $model): string =>
                $model->last_login_at ?: '—',
        ],
        [
            'class' => yii\grid\ActionColumn::class,
            'header' => '',
            'template' => '{view}',
            'buttons' => [
                'view' => static fn($url, User $model): string =>
                    Html::a(
                        'Открыть',
                        ['/user/view', 'id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-primary']
                    ),
            ],
        ],
    ],
]) ?>
