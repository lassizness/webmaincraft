<?php

use common\models\VoteService;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Голосования';
$appUrl = rtrim((string)(Yii::$app->params['appUrl'] ?? ''), '/');
?>

<div class="mb-3">
    <?= Html::a('Добавить сервис', ['edit'], ['class' => 'btn btn-primary']) ?>
</div>

<div class="card p-3 mb-4">
    <h3 class="mb-3">Сервисы голосования</h3>
    <?= GridView::widget([
        'dataProvider' => $serviceProvider,
        'columns' => [
            'id',
            'name',
            'code',
            [
                'attribute' => 'driver',
                'value' => static fn(VoteService $m): string => VoteService::driverOptions()[$m->driver] ?? $m->driver,
            ],
            [
                'attribute' => 'reward_amount',
                'format' => ['decimal', 2],
            ],
            [
                'attribute' => 'reward_cooldown_seconds',
                'label' => 'Кулдаун',
                'value' => static function (VoteService $m): string {
                    $seconds = (int)$m->reward_cooldown_seconds;
                    if ($seconds % 86400 === 0) {
                        return ($seconds / 86400) . ' дн.';
                    }
                    if ($seconds % 3600 === 0) {
                        return ($seconds / 3600) . ' ч.';
                    }
                    return $seconds . ' сек.';
                },
            ],
            'enabled:boolean',
            [
                'attribute' => 'show_on_home',
                'label' => 'На главной',
                'format' => 'boolean',
            ],
            [
                'label' => 'Callback защита',
                'value' => static fn(VoteService $m): string => $m->driver === VoteService::DRIVER_TOPCRAFT
                    ? (trim((string)$m->callback_secret) !== '' ? 'Секрет задан' : 'НЕТ СЕКРЕТА')
                    : '—',
            ],
            'sort',
            [
                'label' => 'Postback URL',
                'format' => 'raw',
                'value' => static fn(VoteService $m): string => $appUrl === ''
                    ? '—'
                    : Html::tag('code', Html::encode($appUrl . '/vote/' . $m->code . '/callback')),
            ],
            [
                'class' => yii\grid\ActionColumn::class,
                'template' => '{update}',
                'buttons' => [
                    'update' => static fn($url, VoteService $m): string => Html::a('✎', ['edit', 'id' => $m->id]),
                ],
            ],
        ],
    ]) ?>
</div>

<div class="card p-3">
    <h3 class="mb-3">Последние голоса</h3>
    <?= GridView::widget([
        'dataProvider' => $voteProvider,
        'columns' => [
            'id',
            [
                'attribute' => 'service_id',
                'label' => 'Сервис',
                'value' => static fn($m): string => (string)($m->service?->name ?? $m->service_id),
            ],
            [
                'attribute' => 'user_id',
                'label' => 'Игрок',
                'value' => static fn($m): string => (string)($m->user?->username ?? ('#' . $m->user_id)),
            ],
            'voter_ip',
            'callback_ip',
            [
                'attribute' => 'provider_vote_id',
                'label' => 'ID/подпись провайдера',
                'value' => static function ($m): string {
                    $value = trim((string)$m->provider_vote_id);
                    return $value === '' ? '—' : (strlen($value) > 20 ? substr($value, 0, 20) . '…' : $value);
                },
            ],
            [
                'attribute' => 'reward_amount',
                'label' => 'Кварки',
                'format' => ['decimal', 2],
            ],
            'rewarded_at',
            'created_at',
        ],
    ]) ?>
</div>
