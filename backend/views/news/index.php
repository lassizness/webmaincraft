<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Новости';
?>

<div class="admin-section-head">
    <div>
        <div class="admin-panel-subtitle">
            Публикации публичного сайта и их категории.
        </div>
    </div>
    <div class="admin-actions">
        <?= Html::a(
            'Категории',
            ['/news-category/index'],
            ['class' => 'btn btn-outline-secondary']
        ) ?>
        <?= Html::a(
            'Добавить новость',
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
        'published_at',
        [
            'class' => yii\grid\ActionColumn::class,
            'template' => '{update} {delete}',
        ],
    ],
]) ?>
