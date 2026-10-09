<?php

use yii\grid\GridView;
use yii\helpers\{Html, Url};

$this->title = 'События игры';
?>

<form class="admin-search-bar mb-3" method="get">
    <div>
        <label class="form-label">Игрок / object / UUID</label>
        <input
            class="form-control"
            name="q"
            value="<?= Html::encode($q) ?>"
            placeholder="Steve, ic2:te, UUID"
        >
    </div>

    <div>
        <label class="form-label">Событие</label>
        <select class="form-select" name="type">
            <option value="">Все</option>
            <?php foreach($eventTypes as $value): ?>
                <option value="<?= Html::encode($value) ?>" <?= $type === $value ? 'selected' : '' ?>>
                    <?= Html::encode($value) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label class="form-label">Источник</label>
        <select class="form-select" name="source">
            <option value="">Все</option>
            <?php foreach($sources as $value): ?>
                <option value="<?= Html::encode($value) ?>" <?= $source === $value ? 'selected' : '' ?>>
                    <?= Html::encode($value) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="admin-search-actions">
        <button class="btn btn-primary">Фильтр</button>
        <a class="btn btn-outline-secondary" href="<?= Url::to(['events']) ?>">Сбросить</a>
    </div>
</form>

<section class="admin-panel p-0 overflow-hidden">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle'],
    'columns' => [
        'id',
        [
            'label' => 'Время',
            'value' => static fn($m) => $m->occurred_at,
        ],
        [
            'label' => 'Игрок',
            'format' => 'raw',
            'value' => static fn($m) => $m->user
                ? Html::a(
                    Html::encode($m->user->username),
                    ['/user/view', 'id' => $m->user_id]
                )
                : Html::encode($m->username ?: '—'),
        ],
        [
            'label' => 'Сервер',
            'value' => static fn($m) => $m->server?->name ?: '—',
        ],
        [
            'attribute' => 'event_type',
            'format' => 'raw',
            'value' => static fn($m) => Html::tag(
                'code',
                Html::encode((string)$m->event_type)
            ),
        ],
        'source',
        [
            'attribute' => 'object_id',
            'format' => 'raw',
            'value' => static fn($m) => $m->object_id
                ? Html::tag('code', Html::encode((string)$m->object_id))
                : '—',
        ],
        'amount',
        [
            'label' => 'Metadata',
            'format' => 'raw',
            'value' => static fn($m) => $m->metadata_json
                ? Html::tag(
                    'code',
                    Html::encode(mb_substr((string)$m->metadata_json, 0, 160)),
                    ['title' => (string)$m->metadata_json, 'class' => 'small']
                )
                : '—',
        ],
    ],
]) ?>
</section>
