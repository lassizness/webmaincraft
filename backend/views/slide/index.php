<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Слайдер';
?>

<div class="admin-section-head">
    <div class="admin-panel-subtitle">
        Промо-слайды и порядок их показа на публичном сайте.
    </div>
    <div class="admin-actions">
        <?= Html::a(
            'Добавить слайд',
            ['create'],
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
            'attribute' => 'title',
            'contentOptions' => ['class' => 'fw-bold'],
        ],
        'image_url',
        [
            'attribute' => 'enabled',
            'format' => 'raw',
            'value' => static fn($model): string => Html::tag(
                'span',
                $model->enabled ? 'Включён' : 'Выключен',
                [
                    'class' => 'badge '
                        .($model->enabled ? 'bg-success' : 'bg-secondary'),
                ]
            ),
        ],
        'sort',
        [
            'class' => yii\grid\ActionColumn::class,
            'template' => '{update} {delete}',
        ],
    ],
]) ?>
