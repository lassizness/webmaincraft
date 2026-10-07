<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Магазин';
?>

<div class="admin-section-head">
    <div class="admin-panel-subtitle">
        Каталог товаров, статусов, цен и способов доставки.
    </div>
    <div class="admin-actions">
        <?= Html::a(
            'Новая категория',
            ['category'],
            ['class' => 'btn btn-outline-secondary']
        ) ?>
        <?= Html::a(
            'Новый товар',
            ['product'],
            ['class' => 'btn btn-primary']
        ) ?>
    </div>
</div>

<section class="admin-panel mb-3">
    <div class="admin-section-head mb-3">
        <div>
            <h2 class="admin-panel-title">Категории</h2>
            <div class="admin-panel-subtitle">
                Быстрый переход к настройке разделов магазина.
            </div>
        </div>
    </div>

    <?php if($categories): ?>
        <div class="d-flex gap-2 flex-wrap">
            <?php foreach($categories as $category): ?>
                <?= Html::a(
                    Html::encode($category->name),
                    ['category', 'id' => $category->id],
                    ['class' => 'btn btn-sm btn-outline-light']
                ) ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="admin-muted small">
            Категорий пока нет.
        </div>
    <?php endif; ?>
</section>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle'],
    'columns' => [
        'id',
        [
            'attribute' => 'name',
            'contentOptions' => ['class' => 'fw-bold'],
        ],
        [
            'attribute' => 'category_id',
            'label' => 'Категория',
            'value' => static fn($model): string =>
                $model->category?->name ?: '—',
        ],
        [
            'attribute' => 'price',
            'contentOptions' => ['class' => 'fw-bold'],
        ],
        'delivery_type',
        [
            'attribute' => 'enabled',
            'format' => 'raw',
            'value' => static fn($model): string => Html::tag(
                'span',
                $model->enabled ? 'Активен' : 'Выключен',
                [
                    'class' => 'badge '
                        .($model->enabled ? 'bg-success' : 'bg-secondary'),
                ]
            ),
        ],
        [
            'class' => yii\grid\ActionColumn::class,
            'template' => '{update}',
            'buttons' => [
                'update' => static fn($url, $model): string =>
                    Html::a(
                        'Изменить',
                        ['product', 'id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-primary']
                    ),
            ],
        ],
    ],
]) ?>
