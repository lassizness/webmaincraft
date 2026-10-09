<?php

use yii\grid\GridView;
use yii\helpers\{Html, Url};

$this->title = 'Сезоны достижений';
?>

<div class="admin-section-head">
    <div class="admin-panel-subtitle">
        Сезонные достижения сохраняются в истории после закрытия сезона.
    </div>
    <div class="admin-actions">
        <a class="btn btn-primary" href="<?= Url::to(['edit']) ?>">
            Создать сезон
        </a>
    </div>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle'],
    'columns' => [
        'id',
        [
            'attribute' => 'title',
            'format' => 'raw',
            'value' => static fn($m) => Html::a(
                Html::encode((string)$m->title),
                ['edit', 'id' => $m->id],
                ['class' => 'fw-bold text-decoration-none']
            ),
        ],
        'code',
        'status',
        'starts_at',
        'ends_at',
        'sort',
    ],
]) ?>
