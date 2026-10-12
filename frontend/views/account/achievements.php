<?php

use common\services\AchievementIconService;
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
    'ae2' => 'AE2',
    'ic2' => 'IC2',
    'forestry' => 'Forestry',
    'thermal' => 'Thermal',
    'immersiveengineering' => 'Immersive Engineering',
    'industrialforegoing' => 'Industrial Foregoing',
    'engineering' => 'Инженерия',
];

$categoryOrder = [
    'general',
    'exploration',
    'mining',
    'building',
    'combat',
    'ae2',
    'ic2',
    'forestry',
    'thermal',
    'immersiveengineering',
    'industrialforegoing',
    'engineering',
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

$catalogMeta = [];
$categoryCounts = [];
$categoryUnlocked = [];
$statusCounts = [
    'all' => 0,
    'unlocked' => 0,
    'progress' => 0,
    'locked' => 0,
];

foreach($catalog as $achievement)
{
    $id = (int)$achievement->id;
    $unlock = $unlocks[$id] ?? null;
    $progressItem = $progress[$id] ?? null;
    $progressPercent = $unlock
        ? 100
        : (int)round(
            (float)($progressItem?->percent ?? 0)
        );

    $status = $unlock
        ? 'unlocked'
        : ($progressPercent > 0
            ? 'progress'
            : 'locked');

    $category = (string)$achievement->category;

    $catalogMeta[$id] = [
        'unlock' => $unlock,
        'progress' => $progressItem,
        'percent' => max(
            0,
            min(100, $progressPercent)
        ),
        'status' => $status,
    ];

    $categoryCounts[$category] =
        ($categoryCounts[$category] ?? 0) + 1;

    if($unlock)
    {
        $categoryUnlocked[$category] =
            ($categoryUnlocked[$category] ?? 0) + 1;
    }

    $statusCounts['all']++;
    $statusCounts[$status]++;
}

$selectedAchievementId = null;

if(!empty($summary['last_unlock_achievement_id']))
{
    $candidate =
        (int)$summary['last_unlock_achievement_id'];

    if(isset($catalogMeta[$candidate]))
    {
        $selectedAchievementId = $candidate;
    }
}

if(
    $selectedAchievementId === null
    && $catalog
)
{
    $selectedAchievementId =
        (int)$catalog[0]->id;
}

$rarityWeight = [
    'common' => 1,
    'uncommon' => 2,
    'rare' => 3,
    'epic' => 4,
    'legendary' => 5,
];

$renderAchievementDetail = static function(
    $achievement,
    $unlock,
    $progressItem,
    int $progressPercent,
    array $categoryLabels
): string {
    $isSecret =
        (bool)$achievement->hidden
        && !$unlock;

    $title = $isSecret
        ? '???'
        : (string)$achievement->title;

    $description = $isSecret
        ? (
            $achievement->hint
            ?: 'Секретное достижение. Условие откроется после выполнения.'
        )
        : (
            $achievement->description
            ?: 'Описание достижения пока не задано.'
        );

    $categoryLabel =
        $categoryLabels[$achievement->category]
        ?? (string)$achievement->category;

    ob_start();
    ?>
    <div class="achievement-detail-content">
        <div class="achievement-detail-icon-wrap">
            <?= AchievementIconService::render(
                $achievement,
                (bool)$unlock,
                'xl',
                null,
                $isSecret
            ) ?>
        </div>

        <div class="achievement-detail-category">
            <?= Html::encode($categoryLabel) ?>
        </div>

        <h3 class="achievement-detail-title">
            <?= Html::encode($title) ?>
        </h3>

        <div class="achievement-detail-badges">
            <span class="achievement-rarity achievement-rarity--<?= Html::encode((string)$achievement->rarity) ?>">
                <?= Html::encode(
                    strtoupper((string)$achievement->rarity)
                ) ?>
            </span>

            <span class="achievement-detail-score">
                +<?= (int)$achievement->score ?> очков
            </span>
        </div>

        <p class="achievement-detail-description">
            <?= Html::encode($description) ?>
        </p>

        <div class="achievement-detail-progress">
            <div class="d-flex justify-content-between gap-3 mb-2">
                <span class="muted">Прогресс</span>
                <strong><?= $progressPercent ?>%</strong>
            </div>

            <div class="progress achievement-progress">
                <div
                    class="progress-bar"
                    role="progressbar"
                    style="width:<?= $progressPercent ?>%"
                    aria-valuenow="<?= $progressPercent ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"
                ></div>
            </div>
        </div>

        <div class="achievement-detail-facts">
            <div>
                <span>Статус</span>
                <strong>
                    <?= $unlock
                        ? 'Получено'
                        : (
                            $progressPercent > 0
                                ? 'В процессе'
                                : 'Не получено'
                        )
                    ?>
                </strong>
            </div>

            <div>
                <span>Награда</span>
                <strong>
                    <?= (int)$achievement->score ?>
                    Achievement Score
                </strong>
            </div>

            <?php if($achievement->season): ?>
                <div>
                    <span>Сезон</span>
                    <strong>
                        <?= Html::encode(
                            (string)$achievement->season->title
                        ) ?>
                    </strong>
                </div>
            <?php endif; ?>

            <?php if($unlock): ?>
                <div>
                    <span>Получено</span>
                    <strong>
                        <?= Html::encode(
                            Yii::$app->formatter->asDatetime(
                                $unlock->unlocked_at,
                                'php:d.m.Y H:i'
                            )
                        ) ?>
                    </strong>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php

    return (string)ob_get_clean();
};
?>

<div class="container ht-section">
    <div class="account-grid">
        <?= $this->render('_nav') ?>

        <section class="achievement-page">
            <div class="achievement-hero">
                <div class="achievement-hero-copy">
                    <div class="eyebrow mb-2">HIVETECH PROGRESS</div>
                    <h1>Достижения</h1>
                    <p>
                        Развивайся, исследуй сборку и собирай коллекцию
                        достижений во всех технологических ветках.
                    </p>
                </div>

                <?php if($publicRatingsEnabled): ?>
                    <a
                        class="btn btn-outline-light achievement-hero-rating"
                        href="<?= Url::to(['/ratings/index']) ?>"
                    >
                        Открыть рейтинг
                    </a>
                <?php endif; ?>

                <div class="achievement-summary-grid">
                    <div class="achievement-summary-card">
                        <span class="achievement-summary-mark">✓</span>
                        <div>
                            <strong>
                                <?= $unlockedCount ?>
                                <small>/ <?= $totalCount ?></small>
                            </strong>
                            <span>Получено</span>
                        </div>
                    </div>

                    <div class="achievement-summary-card">
                        <span class="achievement-summary-mark">◆</span>
                        <div>
                            <strong>
                                <?= number_format(
                                    $score,
                                    0,
                                    ',',
                                    ' '
                                ) ?>
                            </strong>
                            <span>Achievement Score</span>
                        </div>
                    </div>

                    <div class="achievement-summary-card">
                        <span class="achievement-summary-mark">◒</span>
                        <div>
                            <strong><?= $percent ?>%</strong>
                            <span>Общий прогресс</span>
                        </div>
                    </div>

                    <div class="achievement-summary-card">
                        <span class="achievement-summary-mark">#</span>
                        <div>
                            <strong>
                                <?= $rank !== null
                                    ? '#' . (int)$rank
                                    : '—'
                                ?>
                            </strong>
                            <span>
                                <?= $publicRatingsEnabled
                                    ? 'Место в рейтинге'
                                    : 'Рейтинг отключён'
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(!$catalog): ?>
                <div class="ht-card">
                    <div class="muted">
                        Каталог пока пуст. Достижения создаются в админ-панели.
                    </div>
                </div>
            <?php else: ?>
                <div class="achievement-browser">
                    <div class="achievement-toolbar">
                        <div class="achievement-status-tabs" role="group" aria-label="Фильтр достижений">
                            <button
                                class="achievement-filter is-active"
                                type="button"
                                data-achievement-status="all"
                            >
                                Все
                                <span><?= (int)$statusCounts['all'] ?></span>
                            </button>

                            <button
                                class="achievement-filter"
                                type="button"
                                data-achievement-status="unlocked"
                            >
                                Полученные
                                <span><?= (int)$statusCounts['unlocked'] ?></span>
                            </button>

                            <button
                                class="achievement-filter"
                                type="button"
                                data-achievement-status="progress"
                            >
                                В процессе
                                <span><?= (int)$statusCounts['progress'] ?></span>
                            </button>

                            <button
                                class="achievement-filter"
                                type="button"
                                data-achievement-status="locked"
                            >
                                Недоступные
                                <span><?= (int)$statusCounts['locked'] ?></span>
                            </button>
                        </div>

                        <div class="achievement-toolbar-actions">
                            <label class="achievement-search">
                                <span aria-hidden="true">⌕</span>
                                <input
                                    type="search"
                                    placeholder="Поиск достижений..."
                                    data-achievement-search
                                >
                            </label>

                            <select
                                class="form-select achievement-sort"
                                data-achievement-sort
                                aria-label="Сортировка достижений"
                            >
                                <option value="catalog">По каталогу</option>
                                <option value="progress">По прогрессу</option>
                                <option value="rarity">По редкости</option>
                                <option value="name">По названию</option>
                            </select>
                        </div>
                    </div>

                    <div class="achievement-category-strip">
                        <button
                            class="achievement-category is-active"
                            type="button"
                            data-achievement-category="all"
                        >
                            <span class="achievement-category-glyph">✦</span>
                            <span>
                                Все
                                <small>
                                    <?= $unlockedCount ?>/<?= $totalCount ?>
                                </small>
                            </span>
                        </button>

                        <?php foreach($categoryOrder as $category): ?>
                            <?php
                            $count =
                                (int)($categoryCounts[$category] ?? 0);

                            if($count === 0)
                            {
                                continue;
                            }

                            $earned =
                                (int)($categoryUnlocked[$category] ?? 0);
                            ?>
                            <button
                                class="achievement-category"
                                type="button"
                                data-achievement-category="<?= Html::encode($category) ?>"
                            >
                                <span class="achievement-category-glyph">
                                    <?= match($category) {
                                        'ae2' => '◇',
                                        'ic2' => '⚡',
                                        'forestry' => '⬡',
                                        'thermal' => '♨',
                                        'immersiveengineering' => '⚙',
                                        'industrialforegoing' => '▣',
                                        'engineering' => '✥',
                                        'exploration' => '⌖',
                                        'mining' => '◆',
                                        'building' => '▦',
                                        'combat' => '✕',
                                        default => '●',
                                    } ?>
                                </span>

                                <span>
                                    <?= Html::encode(
                                        $categoryLabels[$category]
                                        ?? $category
                                    ) ?>
                                    <small>
                                        <?= $earned ?>/<?= $count ?>
                                    </small>
                                </span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="achievement-browser-meta">
                        <div>
                            <strong data-achievement-visible-count>
                                <?= $totalCount ?>
                            </strong>
                            <span class="muted">
                                достижений найдено
                            </span>
                        </div>

                        <?php if(!empty($summary['last_unlock_title'])): ?>
                            <div class="muted">
                                Последнее:
                                <strong class="text-light">
                                    <?= Html::encode(
                                        (string)$summary['last_unlock_title']
                                    ) ?>
                                </strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="achievement-catalog-layout">
                        <div>
                            <div
                                class="achievement-card-grid"
                                data-achievement-grid
                            >
                                <?php foreach($catalog as $index => $achievement): ?>
                                    <?php
                                    $id = (int)$achievement->id;
                                    $meta = $catalogMeta[$id];
                                    $unlock = $meta['unlock'];
                                    $progressPercent = (int)$meta['percent'];
                                    $status = (string)$meta['status'];
                                    $isSecret =
                                        (bool)$achievement->hidden
                                        && !$unlock;
                                    $displayTitle = $isSecret
                                        ? '???'
                                        : (string)$achievement->title;
                                    $displayDescription = $isSecret
                                        ? (
                                            $achievement->hint
                                            ?: 'Секретное достижение'
                                        )
                                        : (
                                            $achievement->description
                                            ?: 'Без описания'
                                        );
                                    $searchText = $isSecret
                                        ? implode(' ', [
                                            $displayDescription,
                                            $categoryLabels[
                                                $achievement->category
                                            ] ?? $achievement->category,
                                        ])
                                        : implode(' ', [
                                            $achievement->title,
                                            $achievement->description,
                                            $categoryLabels[
                                                $achievement->category
                                            ] ?? $achievement->category,
                                        ]);
                                    ?>
                                    <article
                                        class="achievement-browser-card <?= $unlock ? 'is-earned' : 'is-locked' ?> <?= $id === $selectedAchievementId ? 'is-selected' : '' ?>"
                                        role="button"
                                        tabindex="0"
                                        data-achievement-card
                                        data-achievement-id="<?= $id ?>"
                                        data-achievement-status="<?= Html::encode($status) ?>"
                                        data-achievement-category="<?= Html::encode((string)$achievement->category) ?>"
                                        data-achievement-search="<?= Html::encode(mb_strtolower($searchText)) ?>"
                                        data-achievement-progress="<?= $progressPercent ?>"
                                        data-achievement-rarity="<?= (int)($rarityWeight[$achievement->rarity] ?? 0) ?>"
                                        data-achievement-title="<?= Html::encode(mb_strtolower($displayTitle)) ?>"
                                        data-achievement-order="<?= (int)$index ?>"
                                        aria-label="<?= Html::encode('Открыть достижение ' . $displayTitle) ?>"
                                    >
                                        <div class="achievement-browser-card-top">
                                            <?= AchievementIconService::render(
                                                $achievement,
                                                (bool)$unlock,
                                                'lg',
                                                null,
                                                $isSecret
                                            ) ?>

                                            <div class="achievement-browser-card-copy">
                                                <div class="achievement-browser-card-category">
                                                    <?= Html::encode(
                                                        $categoryLabels[
                                                            $achievement->category
                                                        ] ?? $achievement->category
                                                    ) ?>
                                                </div>

                                                <h3>
                                                    <?= Html::encode(
                                                        $displayTitle
                                                    ) ?>
                                                </h3>
                                            </div>

                                            <?php if($unlock): ?>
                                                <span
                                                    class="achievement-complete-mark"
                                                    title="Получено"
                                                >✓</span>
                                            <?php endif; ?>
                                        </div>

                                        <p>
                                            <?= Html::encode(
                                                $displayDescription
                                            ) ?>
                                        </p>

                                        <div class="achievement-browser-progress-row">
                                            <div class="progress achievement-progress">
                                                <div
                                                    class="progress-bar"
                                                    role="progressbar"
                                                    style="width:<?= $progressPercent ?>%"
                                                    aria-valuenow="<?= $progressPercent ?>"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100"
                                                ></div>
                                            </div>

                                            <span>
                                                <?= $progressPercent ?>%
                                            </span>
                                        </div>

                                        <div class="achievement-browser-card-footer">
                                            <span class="achievement-rarity achievement-rarity--<?= Html::encode((string)$achievement->rarity) ?>">
                                                <?= Html::encode(
                                                    strtoupper(
                                                        (string)$achievement->rarity
                                                    )
                                                ) ?>
                                            </span>

                                            <strong>
                                                +<?= (int)$achievement->score ?>
                                            </strong>

                                            <?php if($unlock): ?>
                                                <span class="muted">
                                                    <?= Html::encode(
                                                        Yii::$app->formatter->asDate(
                                                            $unlock->unlocked_at,
                                                            'php:d.m.Y'
                                                        )
                                                    ) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </article>

                                    <template
                                        data-achievement-template="<?= $id ?>"
                                    >
                                        <?= $renderAchievementDetail(
                                            $achievement,
                                            $unlock,
                                            $meta['progress'],
                                            $progressPercent,
                                            $categoryLabels
                                        ) ?>
                                    </template>
                                <?php endforeach; ?>
                            </div>

                            <div
                                class="achievement-no-results"
                                data-achievement-empty
                                hidden
                            >
                                <div class="achievement-empty-icon">⌕</div>
                                <div>
                                    <strong>
                                        Ничего не найдено
                                    </strong>
                                    <div class="muted small mt-1">
                                        Попробуй изменить фильтр или строку поиска.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <aside
                            class="achievement-detail-panel"
                            data-achievement-detail
                            aria-live="polite"
                        >
                            <?php if($selectedAchievementId !== null): ?>
                                <?php
                                $selectedAchievement = null;

                                foreach($catalog as $candidate)
                                {
                                    if(
                                        (int)$candidate->id
                                        === $selectedAchievementId
                                    )
                                    {
                                        $selectedAchievement =
                                            $candidate;
                                        break;
                                    }
                                }

                                if($selectedAchievement):
                                    $selectedMeta =
                                        $catalogMeta[
                                            $selectedAchievementId
                                        ];
                                ?>
                                    <?= $renderAchievementDetail(
                                        $selectedAchievement,
                                        $selectedMeta['unlock'],
                                        $selectedMeta['progress'],
                                        (int)$selectedMeta['percent'],
                                        $categoryLabels
                                    ) ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </aside>
                    </div>
                </div>
            <?php endif; ?>

            <div class="achievement-secondary-grid mt-4">
                <div class="ht-card">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                        <div>
                            <h3 class="mb-1">
                                Игровая статистика
                            </h3>
                            <p class="muted mb-0">
                                Долговременные показатели HiveTechTelemetry.
                            </p>
                        </div>
                    </div>

                    <?php if(!$stats): ?>
                        <div class="muted">
                            Телеметрия по этому игроку пока не поступала.
                        </div>
                    <?php else: ?>
                        <div class="achievement-stat-grid">
                            <?php
                            $shown = 0;

                            foreach($statLabels as $key => $label):
                                if(!array_key_exists($key, $stats))
                                {
                                    continue;
                                }

                                $shown++;
                            ?>
                                <div class="achievement-stat">
                                    <span>
                                        <?= Html::encode($label) ?>
                                    </span>
                                    <strong>
                                        <?= Html::encode(
                                            $formatStat(
                                                $key,
                                                (int)$stats[$key]
                                            )
                                        ) ?>
                                    </strong>
                                </div>
                            <?php endforeach; ?>

                            <?php if($shown === 0): ?>
                                <?php foreach(array_slice($stats, 0, 8, true) as $key => $value): ?>
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

                <div class="ht-card">
                    <h3 class="mb-1">Последние достижения</h3>
                    <p class="muted mb-3">
                        Последние разблокировки персонажа.
                    </p>

                    <?php if(!$unlocks): ?>
                        <div class="muted">
                            Пока ничего не разблокировано.
                        </div>
                    <?php else: ?>
                        <div class="achievement-recent-list">
                            <?php foreach(array_slice(array_values($unlocks), 0, 6) as $unlock): ?>
                                <?php if(!$unlock->achievement) continue; ?>
                                <div class="achievement-recent-row">
                                    <?= AchievementIconService::render(
                                        $unlock->achievement,
                                        true,
                                        'sm'
                                    ) ?>

                                    <div>
                                        <strong>
                                            <?= Html::encode(
                                                (string)$unlock
                                                    ->achievement
                                                    ->title
                                            ) ?>
                                        </strong>
                                        <span class="muted">
                                            <?= Html::encode(
                                                Yii::$app->formatter
                                                    ->asDatetime(
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

            <div class="ht-card mt-4">
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

<?php
$this->registerJs(<<<'JS'
(function () {
    const root = document.querySelector('.achievement-browser');

    if (!root) {
        return;
    }

    const cards = Array.from(
        root.querySelectorAll('[data-achievement-card]')
    );
    const grid = root.querySelector('[data-achievement-grid]');
    const detail = root.querySelector('[data-achievement-detail]');
    const search = root.querySelector('[data-achievement-search]');
    const sort = root.querySelector('[data-achievement-sort]');
    const empty = root.querySelector('[data-achievement-empty]');
    const visibleCount = root.querySelector('[data-achievement-visible-count]');
    const statusButtons = Array.from(
        root.querySelectorAll('[data-achievement-status]')
    );
    const categoryButtons = Array.from(
        root.querySelectorAll('[data-achievement-category]')
    );

    let activeStatus = 'all';
    let activeCategory = 'all';

    function selectCard(card, scrollDetail) {
        if (!card || card.hidden) {
            return;
        }

        cards.forEach(function (item) {
            item.classList.toggle(
                'is-selected',
                item === card
            );
        });

        const id = card.dataset.achievementId;
        const template = root.querySelector(
            '[data-achievement-template="' + id + '"]'
        );

        if (template && detail) {
            detail.innerHTML = template.innerHTML;
        }

        if (
            scrollDetail
            && window.matchMedia('(max-width: 1050px)').matches
            && detail
        ) {
            detail.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    function sortCards() {
        if (!grid || !sort) {
            return;
        }

        const mode = sort.value;

        cards
            .slice()
            .sort(function (a, b) {
                if (mode === 'progress') {
                    return Number(b.dataset.achievementProgress)
                        - Number(a.dataset.achievementProgress);
                }

                if (mode === 'rarity') {
                    return Number(b.dataset.achievementRarity)
                        - Number(a.dataset.achievementRarity);
                }

                if (mode === 'name') {
                    return a.dataset.achievementTitle.localeCompare(
                        b.dataset.achievementTitle,
                        'ru'
                    );
                }

                return Number(a.dataset.achievementOrder)
                    - Number(b.dataset.achievementOrder);
            })
            .forEach(function (card) {
                grid.appendChild(card);
                const id = card.dataset.achievementId;
                const template = root.querySelector(
                    '[data-achievement-template="' + id + '"]'
                );

                if (template) {
                    grid.appendChild(template);
                }
            });
    }

    function applyFilters() {
        const query = search
            ? search.value.trim().toLocaleLowerCase('ru')
            : '';

        let visible = 0;
        let firstVisible = null;

        cards.forEach(function (card) {
            const statusMatch = activeStatus === 'all'
                || card.dataset.achievementStatus === activeStatus;
            const categoryMatch = activeCategory === 'all'
                || card.dataset.achievementCategory === activeCategory;
            const searchMatch = query === ''
                || card.dataset.achievementSearch.includes(query);

            const show = statusMatch
                && categoryMatch
                && searchMatch;

            card.hidden = !show;

            if (show) {
                visible++;

                if (!firstVisible) {
                    firstVisible = card;
                }
            }
        });

        if (visibleCount) {
            visibleCount.textContent = String(visible);
        }

        if (empty) {
            empty.hidden = visible !== 0;
        }

        const selected = cards.find(function (card) {
            return card.classList.contains('is-selected')
                && !card.hidden;
        });

        if (!selected && firstVisible) {
            selectCard(firstVisible, false);
        }

        if (!firstVisible && detail) {
            detail.innerHTML =
                '<div class="achievement-detail-empty">'
                + '<div class="achievement-empty-icon">⌕</div>'
                + '<strong>Нет достижений по фильтру</strong>'
                + '<span>Измени категорию или параметры поиска.</span>'
                + '</div>';
        }
    }

    statusButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activeStatus = button.dataset.achievementStatus;

            statusButtons.forEach(function (item) {
                item.classList.toggle(
                    'is-active',
                    item === button
                );
            });

            applyFilters();
        });
    });

    categoryButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activeCategory = button.dataset.achievementCategory;

            categoryButtons.forEach(function (item) {
                item.classList.toggle(
                    'is-active',
                    item === button
                );
            });

            applyFilters();
        });
    });

    cards.forEach(function (card) {
        card.addEventListener('click', function () {
            selectCard(card, true);
        });

        card.addEventListener('keydown', function (event) {
            if (
                event.key === 'Enter'
                || event.key === ' '
            ) {
                event.preventDefault();
                selectCard(card, true);
            }
        });
    });

    if (search) {
        search.addEventListener('input', applyFilters);
    }

    if (sort) {
        sort.addEventListener('change', function () {
            sortCards();
            applyFilters();
        });
    }

    sortCards();
    applyFilters();
})();
JS);
?>
