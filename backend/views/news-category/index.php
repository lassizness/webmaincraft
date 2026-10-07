<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Категории новостей';
?>

<div class="admin-section-head">
    <div>
        <?= Html::a(
            '← К новостям',
            ['/news/index'],
            ['class' => 'small text-decoration-none']
        ) ?>
        <div class="admin-panel-subtitle mt-2">
            Справочник категорий для публикаций.
        </div>
    </div>
    <div class="admin-actions">
        <?= Html::a(
            'Добавить категорию',
            ['edit'],
            ['class' => 'btn btn-primary']
        ) ?>
    </div>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle'],
    'columns' => [
        'id',
        [
            'attribute' => 'name',
            'contentOptions' => ['class' => 'fw-bold'],
        ],
        'slug',
        [
            'class' => yii\grid\ActionColumn::class,
            'template' => '{update}',
            'buttons' => [
                'update' => static fn($url, $model): string =>
                    Html::a(
                        'Изменить',
                        ['edit', 'id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-primary']
                    ),
            ],
        ],
    ],
]) ?>
