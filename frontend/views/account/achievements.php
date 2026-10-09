<?php

use yii\helpers\{Html, Url};

$this->title = Yii::t('ui', 'Достижения');

$unlockedCount = (int)$summary['unlocked'];
$totalCount = (int)$summary['total'];
$score = (int)$summary['score'];
$percent = $totalCount > 0
    ? (int)round(($unlockedCount / $totalCount) * 100)
    : 0;

$categoryLabels = [
    'general' => 'Общие',
    'exploration' => 'Исследование',
    'mining' => 'Добыча',
    'building' => 'Строительство',
    'combat' => 'Бой',
    'ic2' => 'IC2',
    'ae2' => 'AE2',
    'thermal' => 'Thermal',
    'forestry' => 'Forestry',
];

$statLabels = [
    'playtime_seconds' => 'Игровое время',
    'blocks.break.total' => 'Блоков сломано',
    'blocks.place.total' => 'Блоков установлено',
    'entity.kill.total' => 'Мобов убито',
    'player.death.total' => 'Смертей',
    'item.craft.total' => 'Предметов создано',
    'item.smelt.total' => 'Предметов переплавлено',
    'dimensions.visited' => 'Измерений посещено',
];

$formatStat = static function(string $key, int $value): string {
    if($key === 'playtime_seconds')
    {
        $hours = intdiv($value, 3600);
        $minutes = intdiv($value % 3600, 60);

        return $hours . ' ч ' . $minutes . ' мин';
    }

    return number_format($value, 0, ',', ' ');
};
?>

<div class="container ht-section">
    <div class="account-grid">
        <?= $this->render('_nav') ?>

        <section>
            <div class="section-title">
                <div>
                    <h2><?= Yii::t('ui', 'Достижения и статистика') ?></h2>
                    <p class="muted mb-0">
                        <?= Yii::t(
                            'ui',
                            'Прогресс, игровая статистика и редкие достижения персонажа.'
                        ) ?>
                    </p>
                </div>

                <?php if($publicRatingsEnabled): ?>
                    <a
                        class="btn btn-outline-light"
                        href="<?= Url::to(['/ratings/index']) ?>"
                    >
                        <?= Yii::t('ui', 'Открыть рейтинги') ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="ht-card h-100">
                        <div class="muted">Получено</div>
                        <div class="metric">
                            <?= $unlockedCount ?> / <?= $totalCount ?>
                        </div>
                        <div class="small muted"><?= $percent ?>% каталога</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="ht-card h-100">
                        <div class="muted">Achievement score</div>
                        <div class="metric"><?= number_format($score, 0, ',', ' ') ?></div>
                        <div class="small muted">Очки за достижения</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="ht-card h-100">
                        <div class="muted">Место в рейтинге</div>
                        <div class="metric">
                            <?= $rank !== null ? '#' . (int)$rank : '—' ?>
                        </div>
                        <div class="small muted">
                            <?= $publicRatingsEnabled
                                ? 'По achievement score'
                                : 'Публичный рейтинг отключён'
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="ht-card h-100">
                        <div class="muted">Последнее достижение</div>
                        <div class="mt-2 fw-bold">
                            <?= Html::encode(
                                $summary['last_unlock'] ?: 'Пока нет'
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ht-card mb-4">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                    <div>
                        <h3 class="mb-1">Игровая статистика</h3>
                        <p class="muted mb-0">
                            Данные появятся после подключения HiveTechTelemetry.
                        </p>
                    </div>
                </div>

                <?php if(!$stats): ?>
                    <div class="muted">
                        Телеметрия по этому игроку пока не поступала.
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php
                        $shown = 0;
                        foreach($statLabels as $key => $label):
                            if(!array_key_exists($key, $stats))
                            {
                                continue;
                            }

                            $shown++;
                        ?>
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="muted small">
                                        <?= Html::encode($label) ?>
                                    </div>
                                    <div class="fs-4 fw-bold mt-1">
                                        <?= Html::encode(
                                            $formatStat(
                                                $key,
                                                (int)$stats[$key]
                                            )
                                        ) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if($shown === 0): ?>
                            <?php foreach(array_slice($stats, 0, 9, true) as $key => $value): ?>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="muted small font-monospace">
                                            <?= Html::encode((string)$key) ?>
                                        </div>
                                        <div class="fs-4 fw-bold mt-1">
                                            <?= number_format((int)$value, 0, ',', ' ') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="ht-card mb-4">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                    <div>
                        <h3 class="mb-1">Каталог достижений</h3>
                        <p class="muted mb-0">
                            Общие, технологические и сезонные достижения.
                        </p>
                    </div>
                </div>

                <?php if(!$catalog): ?>
                    <div class="muted">
                        Каталог пока пуст. Достижения создаются в админ-панели.
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach($catalog as $achievement): ?>
                            <?php
                            $unlock = $unlocks[(int)$achievement->id] ?? null;
                            $progressItem = $progress[(int)$achievement->id] ?? null;
                            $progressPercent = $unlock
                                ? 100
                                : (int)round((float)($progressItem?->percent ?? 0));
                            $isSecret = (bool)$achievement->hidden && !$unlock;
                            ?>
                            <div class="col-lg-6">
                                <div class="ht-card h-100 border">
                                    <div class="d-flex justify-content-between gap-3 align-items-start">
                                        <div>
                                            <div class="small text-uppercase muted mb-1">
                                                <?= Html::encode(
                                                    $categoryLabels[$achievement->category]
                                                    ?? $achievement->category
                                                ) ?>
                                            </div>
                                            <h4 class="mb-1">
                                                <?= Html::encode(
                                                    $isSecret
                                                        ? '???'
                                                        : $achievement->title
                                                ) ?>
                                            </h4>
                                        </div>

                                        <span class="badge text-bg-secondary">
                                            <?= Html::encode(
                                                strtoupper($achievement->rarity)
                                            ) ?>
                                        </span>
                                    </div>

                                    <p class="muted mt-2 mb-3">
                                        <?= Html::encode(
                                            $isSecret
                                                ? ($achievement->hint ?: 'Секретное достижение')
                                                : ($achievement->description ?: 'Без описания')
                                        ) ?>
                                    </p>

                                    <div class="progress mb-2" style="height:8px">
                                        <div
                                            class="progress-bar"
                                            role="progressbar"
                                            style="width:<?= max(0, min(100, $progressPercent)) ?>%"
                                            aria-valuenow="<?= $progressPercent ?>"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                        ></div>
                                    </div>

                                    <div class="d-flex justify-content-between gap-3 small">
                                        <span class="muted">
                                            <?= $unlock
                                                ? 'Получено ' . Html::encode((string)$unlock->unlocked_at)
                                                : $progressPercent . '%'
                                            ?>
                                        </span>
                                        <strong>+<?= (int)$achievement->score ?> очков</strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="ht-card mb-4">
                <h3 class="mb-1">Последние достижения</h3>
                <p class="muted mb-3">
                    История разблокировок персонажа.
                </p>

                <?php if(!$unlocks): ?>
                    <div class="muted">Пока ничего не разблокировано.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <tbody>
                            <?php foreach(array_slice(array_values($unlocks), 0, 20) as $unlock): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?= Html::encode(
                                                $unlock->achievement?->title
                                                ?: ('#' . $unlock->achievement_id)
                                            ) ?>
                                        </strong>
                                    </td>
                                    <td class="text-end">
                                        +<?= (int)$unlock->score_awarded ?>
                                    </td>
                                    <td class="text-end muted">
                                        <?= Html::encode((string)$unlock->unlocked_at) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="ht-card">
                <h3 class="mb-1">Публичный профиль</h3>
                <p class="muted mb-3">
                    Эти настройки применяются только если публичные рейтинги включены администрацией проекта.
                </p>

                <?= Html::beginForm(['/account/achievements'], 'post') ?>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <?= Html::checkbox(
                                'public_achievements',
                                (bool)$profileSettings->public_achievements,
                                [
                                    'value' => '1',
                                    'label' => 'Показывать достижения',
                                ]
                            ) ?>
                        </div>

                        <div class="col-md-4">
                            <?= Html::checkbox(
                                'public_playtime',
                                (bool)$profileSettings->public_playtime,
                                [
                                    'value' => '1',
                                    'label' => 'Показывать игровое время',
                                ]
                            ) ?>
                        </div>

                        <div class="col-md-4">
                            <?= Html::checkbox(
                                'public_ranking',
                                (bool)$profileSettings->public_ranking,
                                [
                                    'value' => '1',
                                    'label' => 'Участвовать в рейтинге',
                                ]
                            ) ?>
                        </div>
                    </div>

                    <button class="btn btn-primary mt-3" type="submit">
                        Сохранить
                    </button>
                <?= Html::endForm() ?>
            </div>
        </section>
    </div>
</div>
