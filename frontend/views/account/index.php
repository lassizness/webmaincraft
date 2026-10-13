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

            <div class="ht-card mt-4">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <h3 class="mb-1">
                            <?= Yii::t('ui', 'Голосование за сервер') ?>
                        </h3>
                        <p class="muted mb-0">
                            <?= Yii::t('ui', 'Поддержи HiveGrid голосом и получай кварки.') ?>
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap justify-content-end">
                        <?php foreach (($voteServices ?? []) as $voteService): ?>
                            <a
                                class="btn btn-outline-light"
                                href="<?= Url::to(['/vote/index']) ?>#vote-service-<?= Html::encode((string)$voteService->code) ?>"
                            >
                                <?= Html::encode((string)$voteService->name) ?>
                            </a>
                        <?php endforeach; ?>

                        <a class="btn btn-primary" href="<?= Url::to(['/vote/index']) ?>">
                            <?= Yii::t('ui', 'Все рейтинги') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="ht-card mt-4">

                <h3>
                    <?= Yii::t('ui', 'Последние покупки') ?>
                </h3>

                <?php if(!$purchases): ?>

                    <p class="muted">
                        <?= Yii::t('ui', 'Покупок пока нет.') ?>
                    </p>

                <?php else: ?>

                    <table class="table">
                        <tbody>

                        <?php foreach($purchases as $purchase): ?>
                            <tr>
                                <td>#<?= $purchase->id ?></td>

                                <td>
                                    <?= Html::encode(
                                        $purchase->product
                                            ? $translateShopText((string)$purchase->product->name)
                                            : Yii::t('ui', 'Товар')
                                    ) ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        (float)$purchase->price,
                                        2,
                                        ',',
                                        ' '
                                    ) ?>
                                </td>

                                <td>
                                    <?= Html::encode(
                                        $purchase->status === 'paid'
                                            ? Yii::t('ui', 'Оплачено')
                                            : $purchase->status
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        </tbody>
                    </table>

                <?php endif; ?>

            </div>

        </section>
    </div>
</div>
