<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Страницы';
?>

<div class="admin-section-head">
    <div class="admin-panel-subtitle">
        Статические разделы сайта: правила, гайды и служебные страницы.
    </div>
    <div class="admin-actions">
        <?= Html::a(
            'Добавить страницу',
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
        'slug',
        [
            'attribute' => 'published',
            'format' => 'raw',
            'value' => static fn($model): string => Html::tag(
                'span',
                $model->published ? 'Опубликована' : 'Черновик',
                [
                    'class' => 'badge '
                        .($model->published ? 'bg-success' : 'bg-secondary'),
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
