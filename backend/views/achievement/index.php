<?php

use yii\grid\GridView;
use yii\helpers\{Html, Url};

$this->title = 'Достижения';
?>

<div class="admin-section-head">
    <div>
        <div class="admin-panel-subtitle">
            Каталог достижений. Условия основаны на агрегированных stat_key,
            поэтому новые достижения не требуют пересборки игрового мода.
        </div>
    </div>
    <div class="admin-actions">
        <a class="btn btn-primary" href="<?= Url::to(['edit']) ?>">
            Создать достижение
        </a>
    </div>
</div>

<form class="admin-search-bar mb-3" method="get">
    <div>
        <label class="form-label">Категория</label>
        <input
            class="form-control"
            name="category"
            value="<?= Html::encode($category) ?>"
            placeholder="general, ae2, ic2, forestry, thermal, immersiveengineering, industrialforegoing"
        >
    </div>
    <div class="admin-search-actions">
        <button class="btn btn-primary">Фильтр</button>
        <a class="btn btn-outline-secondary" href="<?= Url::to(['index']) ?>">Сбросить</a>
    </div>
</form>

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
        [
            'attribute' => 'code',
            'format' => 'raw',
            'value' => static fn($m) => Html::tag('code', Html::encode((string)$m->code)),
        ],
        'category',
        'rarity',
        'score',
        [
            'label' => 'Сезон',
            'value' => static fn($m) => $m->season?->title ?: 'Постоянное',
        ],
        [
            'attribute' => 'hidden',
            'value' => static fn($m) => $m->hidden ? 'Да' : 'Нет',
        ],
        [
            'attribute' => 'enabled',
            'format' => 'raw',
            'value' => static fn($m) => Html::tag(
                'span',
                $m->enabled ? 'Активно' : 'Выключено',
                ['class' => 'badge ' . ($m->enabled ? 'bg-success' : 'bg-secondary')]
            ),
        ],
        [
            'label' => '',
            'format' => 'raw',
            'value' => static fn($m) =>
                Html::beginForm(['toggle', 'id' => $m->id], 'post', ['class' => 'd-inline'])
                . Html::submitButton(
                    $m->enabled ? 'Выключить' : 'Включить',
                    ['class' => 'btn btn-sm btn-outline-secondary']
                )
                . Html::endForm(),
        ],
    ],
]) ?>
