<?php

use yii\grid\GridView;
use yii\helpers\{Html, Url};

$this->title = 'HWID';
?>

<div class="admin-section-head">
    <div class="admin-panel-subtitle">
        Устройства GravitLauncher: привязки игроков, аппаратные признаки и блокировка HWID.
    </div>
</div>

<form class="admin-search-bar mb-3" method="get">
    <div>
        <label class="form-label">Поиск</label>
        <input class="form-control" name="q" value="<?= Html::encode($q) ?>"
               placeholder="Ник, диск, плата или видеокарта">
    </div>
    <div>
        <label class="form-label">Блокировка</label>
        <select class="form-select" name="banned">
            <option value="">Все</option>
            <option value="0" <?= $banned === '0' ? 'selected' : '' ?>>Разрешены</option>
            <option value="1" <?= $banned === '1' ? 'selected' : '' ?>>Заблокированы</option>
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
            'label' => 'Игроки',
            'value' => static fn($m) => implode(', ', array_map(
                static fn($u) => (string)$u->username,
                $m->users
            )) ?: '—',
        ],
        'hwDiskId',
        'baseboardSerialNumber',
        'graphicCard',
        [
            'label' => 'RAM',
            'value' => static fn($m) => $m->totalMemory
                ? round((int)$m->totalMemory / 1024 / 1024 / 1024, 1) . ' GB'
                : '—',
        ],
        [
            'attribute' => 'banned',
            'format' => 'raw',
            'value' => static fn($m) => Html::tag(
                'span',
                $m->banned ? 'BANNED' : 'OK',
                ['class' => 'badge ' . ($m->banned ? 'bg-danger' : 'bg-success')]
            ),
        ],
        [
            'label' => '',
            'format' => 'raw',
            'value' => static fn($m) => Html::a(
                'Открыть',
                ['view', 'id' => $m->id],
                ['class' => 'btn btn-sm btn-outline-primary']
            ),
        ],
    ],
]) ?>
