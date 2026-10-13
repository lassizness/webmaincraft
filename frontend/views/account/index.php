<?php

use common\services\AchievementIconService;
use yii\helpers\{Html, Url};

$this->title = Yii::t('ui', 'Личный кабинет');

$status = Yii::t(
    'ui',
    (string)$user->getDisplayStatus()
);

$translateShopText = static function (?string $value): string {
    $value = trim((string)$value);

    if($value === '' || Yii::$app->language === 'ru')
    {
        return $value;
    }

    $normalized = preg_replace('/\\s+/u', ' ', $value);

    if(!is_string($normalized) || $normalized === '')
    {
        return $value;
    }

    if(preg_match(
        '/^(VIP|Premium|Elite|Legend)\\s+на\\s+(\\d+)\\s+дн(?:я|ей)?\\.?$/u',
        $normalized,
        $matches
    ))
    {
        return Yii::t('ui', '{status} на {days} дней', [
            'status' => $matches[1],
            'days' => (int)$matches[2],
        ]);
    }

    $translated = Yii::t('ui', $normalized);

    return $translated !== $normalized
        ? $translated
        : $value;
};

$formatQuarks = static function (float $amount): string {
    $precision = abs($amount - round($amount)) < 0.00001 ? 0 : 2;
    return Yii::$app->formatter->asDecimal($amount, $precision);
};

$purchaseStatusLabels = [
    'paid' => Yii::t('ui', 'Оплачено'),
    'new' => Yii::t('ui', 'В обработке'),
    'pending' => Yii::t('ui', 'В обработке'),
    'cancelled' => Yii::t('ui', 'Отменено'),
    'failed' => Yii::t('ui', 'Ошибка'),
];

$achievementStatLabels = [
    'playtime_seconds' => Yii::t('ui', 'Игровое время'),
    'blocks.break.total' => Yii::t('ui', 'Блоков сломано'),
    'blocks.place.total' => Yii::t('ui', 'Блоков установлено'),
    'entity.kill.total' => Yii::t('ui', 'Мобов убито'),
    'player.death.total' => Yii::t('ui', 'Смертей'),
    'item.craft.total' => Yii::t('ui', 'Предметов создано'),
    'item.smelt.total' => Yii::t('ui', 'Предметов переплавлено'),
    'dimensions.visited' => Yii::t('ui', 'Измерений посещено'),
];

$formatAchievementStat = static function (
    string $key,
    int $value
): string {
    if($key === 'playtime_seconds')
    {
        $hours = intdiv($value, 3600);
        $minutes = intdiv($value % 3600, 60);

        return Yii::t('ui', '{hours} ч {minutes} мин', [
            'hours' => $hours,
            'minutes' => $minutes,
        ]);
    }

    return number_format($value, 0, ',', ' ');
};


?>

<div class="container ht-section">
    <div class="account-grid">

        <?= $this->render('_nav') ?>

        <section>

            <?php if(!(bool)$user->email_verified): ?>
                <div class="alert alert-warning">
                    <?= Yii::t(
                        'ui',
                        'Email не подтверждён. Это не мешает входу, но рекомендуем подтвердить адрес для восстановления доступа и уведомлений.'
                    ) ?>
                    <a
                        class="alert-link ms-1"
                        href="<?= Url::to(['/auth/resend-verification']) ?>"
                    >
                        <?= Yii::t('ui', 'Отправить письмо повторно') ?>
                    </a>
                </div>
            <?php endif; ?>

            <div class="section-title">
                <h2>
                    <?= Yii::t('ui', 'Привет, {username}', [
                        'username' => Html::encode($user->username),
                    ]) ?>
                </h2>

                <span class="badge text-bg-success">
                    <?= Html::encode($status) ?>
                </span>
            </div>

            <div class="row g-3">

                <div class="col-md-6">
                    <div class="ht-card">
                        <div class="muted">UUID</div>
                        <div class="small mt-2 font-monospace">
                            <?= Html::encode($user->uuid) ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="ht-card">
                        <div class="muted">
                            <?= Yii::t('ui', 'Баланс') ?>
                        </div>

                        <div class="metric">
                            <?= number_format(
                                (float)$wallet->balance,
                                2,
                                ',',
                                ' '
                            ) ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="ht-card">
                        <div class="muted">Email</div>
                        <div class="mt-2">
                            <?= Html::encode($user->email) ?>
                        </div>
                    </div>
                </div>

            </div>

            <?php if($achievementsEnabled): ?>
                <div class="account-overview-grid mt-4">
                    <div class="ht-card achievement-overview-stats">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                            <div>
                                <div class="eyebrow mb-1">
                                    <?= Yii::t('ui', 'ИГРОВАЯ СТАТИСТИКА') ?>
                                </div>
                                <h3 class="mb-1">
                                    <?= Yii::t('ui', 'Игровая статистика') ?>
                                </h3>
                                <p class="muted mb-0">
                                    <?= Yii::t('ui', 'Долговременные показатели HiveTechTelemetry.') ?>
                                </p>
                            </div>

                            <a
                                class="btn btn-outline-light btn-sm"
                                href="<?= Url::to(['/account/achievements']) ?>"
                            >
                                <?= Yii::t('ui', 'Достижения') ?>
                            </a>
                        </div>

                        <?php if(!$achievementStats): ?>
                            <div class="muted">
                                <?= Yii::t('ui', 'Телеметрия по этому игроку пока не поступала.') ?>
                            </div>
                        <?php else: ?>
                            <div class="achievement-stat-grid">
                                <?php
                                $shownStats = 0;

                                foreach($achievementStatLabels as $key => $label):
                                    if(!array_key_exists($key, $achievementStats))
                                    {
                                        continue;
                                    }

                                    $shownStats++;
                                ?>
                                    <div class="achievement-stat">
                                        <span>
                                            <?= Html::encode($label) ?>
                                        </span>
                                        <strong>
                                            <?= Html::encode(
                                                $formatAchievementStat(
                                                    $key,
                                                    (int)$achievementStats[$key]
                                                )
                                            ) ?>
                                        </strong>
                                    </div>
                                <?php endforeach; ?>

                                <?php if($shownStats === 0): ?>
                                    <?php foreach(array_slice($achievementStats, 0, 6, true) as $key => $value): ?>
                                        <div class="achievement-stat">
                                            <span class="font-monospace">
                                                <?= Html::encode((string)$key) ?>
                                            </span>
                                            <strong>
                                                <?= number_format(
                                                    (int)$value,
                                                    0,
                                                    ',',
                                                    ' '
                                                ) ?>
                                            </strong>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="ht-card achievement-dashboard">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                            <div>
                                <div class="eyebrow mb-1">ACHIEVEMENTS</div>
                                <h3 class="mb-1">
                                    <?= Yii::t('ui', 'Последние достижения') ?>
                                </h3>
                                <p class="muted mb-0">
                                    <?= Yii::t('ui', '{unlocked} из {total} · {score} очков', [
                                        'unlocked' => (int)($achievementSummary['unlocked'] ?? 0),
                                        'total' => (int)($achievementSummary['total'] ?? 0),
                                        'score' => number_format(
                                            (int)($achievementSummary['score'] ?? 0),
                                            0,
                                            ',',
                                            ' '
                                        ),
                                    ]) ?>
                                </p>
                            </div>

                            <a
                                class="btn btn-outline-light btn-sm"
                                href="<?= Url::to(['/account/achievements']) ?>"
                            >
                                <?= Yii::t('ui', 'Все достижения') ?>
                            </a>
                        </div>

                        <?php if(!$recentAchievements): ?>
                            <div class="achievement-empty">
                                <div class="achievement-empty-icon">✦</div>
                                <div>
                                    <strong>
                                        <?= Yii::t('ui', 'Коллекция пока пуста') ?>
                                    </strong>
                                    <div class="muted small mt-1">
                                        <?= Yii::t('ui', 'Играй на сервере, развивай технологии и открывай достижения.') ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="achievement-recent-list">
                                <?php foreach($recentAchievements as $unlock): ?>
                                    <?php $achievement = $unlock->achievement; ?>
                                    <?php if(!$achievement) continue; ?>

                                    <div class="achievement-recent-row">
                                        <?= AchievementIconService::render(
                                            $achievement,
                                            true,
                                            'sm'
                                        ) ?>

                                        <div>
                                            <strong>
                                                <?= Html::encode(
                                                    $achievement->getDisplayTitle()
                                                ) ?>
                                            </strong>
                                            <span class="muted">
                                                <?= Html::encode(
                                                    Yii::$app->formatter->asDatetime(
                                                        $unlock->unlocked_at,
                                                        'php:d.m.Y H:i'
                                                    )
                                                ) ?>
                                            </span>
                                        </div>

                                        <b>
                                            +<?= (int)$unlock->score_awarded ?>
                                        </b>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="account-activity-grid mt-4">
                <section class="ht-card account-activity-card account-vote-panel" aria-labelledby="account-vote-heading">
                    <div class="account-activity-heading">
                        <div class="account-activity-heading-copy">
                            <span class="eyebrow">COMMUNITY</span>
                            <h3 id="account-vote-heading">
                                <?= Yii::t('ui', 'Голосование за сервер') ?>
                            </h3>
                            <p class="muted">
                                <?= Yii::t('ui', 'Поддержи HiveGrid голосом и получай кварки.') ?>
                            </p>
                        </div>

                        <a class="btn btn-outline-light btn-sm account-activity-all" href="<?= Url::to(['/vote/index']) ?>">
                            <?= Yii::t('ui', 'Все рейтинги') ?>
                            <span aria-hidden="true">↗</span>
                        </a>
                    </div>

                    <?php if(empty($voteServices)): ?>
                        <div class="account-activity-empty">
                            <span class="account-activity-empty-mark" aria-hidden="true">✦</span>
                            <p class="muted mb-0">
                                <?= Yii::t('ui', 'Сервисы голосования пока не настроены.') ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="account-vote-list">
                            <?php foreach($voteServices as $voteService): ?>
                                <a
                                    class="account-vote-link"
                                    href="<?= Url::to(['/vote/index']) ?>#vote-service-<?= rawurlencode((string)$voteService->code) ?>"
                                >
                                    <span class="account-vote-mark" aria-hidden="true">✦</span>
                                    <span class="account-vote-copy">
                                        <strong><?= Html::encode((string)$voteService->name) ?></strong>
                                        <small class="muted">
                                            +<?= Html::encode($formatQuarks((float)$voteService->reward_amount)) ?>
                                            <?= Yii::t('ui', 'кварков') ?>
                                        </small>
                                    </span>
                                    <span class="account-vote-cta">
                                        <?= Yii::t('ui', 'Голосовать') ?>
                                        <span aria-hidden="true">↗</span>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="ht-card account-activity-card account-purchases-panel" aria-labelledby="account-purchases-heading">
                    <div class="account-activity-heading">
                        <div class="account-activity-heading-copy">
                            <span class="eyebrow">STORE</span>
                            <h3 id="account-purchases-heading">
                                <?= Yii::t('ui', 'Последние покупки') ?>
                            </h3>
                            <p class="muted">
                                <?= Yii::t('ui', 'История последних заказов в магазине.') ?>
                            </p>
                        </div>

                        <a class="btn btn-outline-light btn-sm account-activity-all" href="<?= Url::to(['/shop/index']) ?>">
                            <?= Yii::t('ui', 'Магазин') ?>
                            <span aria-hidden="true">↗</span>
                        </a>
                    </div>

                    <?php if(empty($purchases)): ?>
                        <div class="account-activity-empty">
                            <span class="account-activity-empty-mark account-activity-empty-mark--shop" aria-hidden="true">◇</span>
                            <p class="muted mb-0">
                                <?= Yii::t('ui', 'Покупок пока нет.') ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="account-purchase-list">
                            <?php foreach($purchases as $purchase): ?>
                                <?php
                                $purchaseStatus = (string)$purchase->status;
                                $purchaseStatusClass = in_array(
                                    $purchaseStatus,
                                    ['paid', 'new', 'pending', 'cancelled', 'failed'],
                                    true
                                ) ? $purchaseStatus : 'other';
                                $purchaseStatusLabel = $purchaseStatusLabels[$purchaseStatus]
                                    ?? $purchaseStatus;
                                ?>
                                <div class="account-purchase-row">
                                    <div class="account-purchase-info">
                                        <strong>
                                            <?= Html::encode(
                                                $purchase->product
                                                    ? $translateShopText((string)$purchase->product->name)
                                                    : Yii::t('ui', 'Товар')
                                            ) ?>
                                        </strong>
                                        <span class="account-purchase-meta">
                                            #<?= (int)$purchase->id ?>
                                            <span aria-hidden="true">·</span>
                                            <?= Html::encode(
                                                Yii::$app->formatter->asDate(
                                                    $purchase->created_at,
                                                    'php:d.m.Y'
                                                )
                                            ) ?>
                                        </span>
                                    </div>

                                    <div class="account-purchase-right">
                                        <strong class="account-purchase-price">
                                            <?= Html::encode($formatQuarks((float)$purchase->price)) ?>
                                            <span><?= Yii::t('ui', 'кварков') ?></span>
                                        </strong>
                                        <span class="account-purchase-status account-purchase-status--<?= Html::encode($purchaseStatusClass) ?>">
                                            <?= Html::encode($purchaseStatusLabel) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

        </section>
    </div>
</div>
