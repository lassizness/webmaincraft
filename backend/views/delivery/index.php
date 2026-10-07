<?php

use yii\grid\GridView;
use yii\helpers\{Html, Url};

$this->title = 'Очередь выдачи';
?>

<div class="admin-section-head">
    <div class="admin-panel-subtitle">
        RCON и другие задачи выдачи. Failed можно безопасно вернуть в очередь,
        а force retry дополнительно сбрасывает счётчик попыток.
    </div>
</div>

<form class="admin-search-bar mb-3" method="get">
    <div>
        <label class="form-label">Поиск</label>
        <input class="form-control" name="q" value="<?= Html::encode($q) ?>"
               placeholder="ID задачи, покупки, пользователя или ник">
    </div>
    <div>
        <label class="form-label">Статус</label>
        <select class="form-select" name="status">
            <option value="">Все</option>
            <?php foreach(['pending','processing','done','failed'] as $value): ?>
                <option value="<?= $value ?>" <?= $status === $value ? 'selected' : '' ?>>
                    <?= Html::encode($value) ?>
                </option>
            <?php endforeach; ?>
        </select>
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
            'label' => 'Игрок',
            'value' => static fn($m) => $m->user?->username ?: ('#' . $m->user_id),
        ],
        [
            'label' => 'Сервер',
            'value' => static fn($m) => $m->server?->name ?: '—',
        ],
        'delivery_type',
        [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => static function($m): string {
                $class = match($m->status) {
                    'done' => 'bg-success',
                    'failed' => 'bg-danger',
                    'processing' => 'bg-info',
                    default => 'bg-secondary',
                };
                return Html::tag('span', Html::encode($m->status), ['class' => 'badge ' . $class]);
            },
        ],
        'attempts',
        'available_at',
        'processed_at',
        [
            'attribute' => 'last_error',
            'format' => 'raw',
            'value' => static fn($m) => $m->last_error
                ? Html::tag('code', Html::encode(mb_substr((string)$m->last_error, 0, 180)), [
                    'title' => (string)$m->last_error,
                    'class' => 'small',
                ])
                : '—',
        ],
        [
            'label' => 'Payload',
            'format' => 'raw',
            'value' => static fn($m) => Html::tag(
                'code',
                Html::encode(mb_substr((string)$m->payload, 0, 120)),
                ['title' => (string)$m->payload, 'class' => 'small']
            ),
        ],
        [
            'label' => '',
            'format' => 'raw',
            'value' => static function($m): string {
                if($m->status === 'done')
                {
                    return '';
                }

                return Html::beginForm(['retry', 'id' => $m->id], 'post', ['class' => 'd-inline'])
                    . Html::submitButton('Retry', ['class' => 'btn btn-sm btn-outline-primary'])
                    . Html::endForm()
                    . ' '
                    . Html::beginForm(['force-retry', 'id' => $m->id], 'post', ['class' => 'd-inline'])
                    . Html::submitButton('Force', [
                        'class' => 'btn btn-sm btn-outline-danger',
                        'data-confirm' => 'Сбросить attempts и принудительно вернуть задачу в очередь?',
                    ])
                    . Html::endForm();
            },
        ],
    ],
]) ?>
