<?php

use common\models\ModerationAction;
use yii\grid\GridView;
use yii\helpers\{Html, Url};

$this->title = 'Игровая модерация';

$typeLabels = [
    ModerationAction::TYPE_WARN => 'Warn',
    ModerationAction::TYPE_MUTE => 'Mute',
    ModerationAction::TYPE_BAN => 'Ban',
];

$statusLabels = [
    ModerationAction::STATUS_ACTIVE => 'Активно',
    ModerationAction::STATUS_RECORDED => 'Записано',
    ModerationAction::STATUS_EXPIRED => 'Истекло',
    ModerationAction::STATUS_REVOKED => 'Снято',
];
?>

<div class="admin-section-head">
    <div class="admin-panel-subtitle">
        Источник истины по игровым наказаниям. Account block здесь не используется:
        игрок сохраняет доступ к сайту и поддержке.
    </div>

    <div class="admin-actions">
        <?php if(
            Yii::$app->user->can('createWarn')
            || Yii::$app->user->can('createMute')
            || Yii::$app->user->can('createBan')
        ): ?>
            <?= Html::a(
                'Новое наказание',
                ['create'],
                ['class' => 'btn btn-primary']
            ) ?>
        <?php endif; ?>
    </div>
</div>

<form class="admin-search-bar mb-3" method="get">
    <div>
        <label class="form-label">Поиск</label>
        <input
            class="form-control"
            name="q"
            value="<?= Html::encode($filters['q']) ?>"
            placeholder="Ник, ID или причина"
        >
    </div>

    <div>
        <label class="form-label">Тип</label>
        <select class="form-select" name="type">
            <option value="">Все</option>
            <?php foreach($typeLabels as $value => $label): ?>
                <option
                    value="<?= Html::encode($value) ?>"
                    <?= $filters['type'] === $value ? 'selected' : '' ?>
                >
                    <?= Html::encode($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label class="form-label">Статус</label>
        <select class="form-select" name="status">
            <option value="">Все</option>
            <?php foreach($statusLabels as $value => $label): ?>
                <option
                    value="<?= Html::encode($value) ?>"
                    <?= $filters['status'] === $value ? 'selected' : '' ?>
                >
                    <?= Html::encode($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label class="form-label">Область</label>
        <select class="form-select" name="scope">
            <option value="">Все</option>
            <option value="server" <?= $filters['scope'] === 'server' ? 'selected' : '' ?>>
                Сервер
            </option>
            <option value="global" <?= $filters['scope'] === 'global' ? 'selected' : '' ?>>
                Глобально
            </option>
        </select>
    </div>

    <div>
        <label class="form-label">Сервер</label>
        <select class="form-select" name="server_id">
            <option value="">Все</option>
            <?php foreach($servers as $server): ?>
                <option
                    value="<?= (int)$server->id ?>"
                    <?= (int)$filters['server_id'] === (int)$server->id ? 'selected' : '' ?>
                >
                    <?= Html::encode($server->name) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="admin-search-actions">
        <button class="btn btn-primary">Фильтр</button>
        <a class="btn btn-outline-secondary" href="<?= Url::to(['index']) ?>">
            Сбросить
        </a>
    </div>
</form>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle'],
    'columns' => [
        'id',
        [
            'label' => 'Игрок',
            'format' => 'raw',
            'value' => static fn($m): string => $m->user
                ? Html::a(
                    Html::encode($m->user->username),
                    ['/user/view', 'id' => $m->user_id],
                    ['class' => 'text-decoration-none']
                )
                : '#' . $m->user_id,
        ],
        [
            'label' => 'Тип',
            'value' => static fn($m) => $typeLabels[$m->type] ?? $m->type,
        ],
        [
            'label' => 'Область',
            'value' => static fn($m) => $m->scope === 'global'
                ? 'Все серверы'
                : ($m->server?->name ?: 'Сервер'),
        ],
        [
            'attribute' => 'status',
            'label' => 'Статус',
            'format' => 'raw',
            'value' => static function($m) use ($statusLabels): string {
                $class = match($m->status) {
                    ModerationAction::STATUS_ACTIVE => 'bg-danger',
                    ModerationAction::STATUS_RECORDED => 'bg-warning',
                    ModerationAction::STATUS_EXPIRED => 'bg-secondary',
                    ModerationAction::STATUS_REVOKED => 'bg-success',
                    default => 'bg-secondary',
                };

                return Html::tag(
                    'span',
                    Html::encode($statusLabels[$m->status] ?? $m->status),
                    ['class' => 'badge ' . $class]
                );
            },
        ],
        [
            'attribute' => 'reason',
            'value' => static fn($m) => mb_substr((string)$m->reason, 0, 90),
        ],
        'expires_at',
        'created_at',
        [
            'label' => '',
            'format' => 'raw',
            'value' => static fn($m): string => Html::a(
                'Открыть',
                ['view', 'id' => $m->id],
                ['class' => 'btn btn-sm btn-outline-primary']
            ),
        ],
    ],
]) ?>
