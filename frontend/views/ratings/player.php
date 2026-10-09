<?php

use yii\helpers\Html;

$this->title = 'Игрок ' . $user->username;

$playtime = (int)($stats['playtime_seconds'] ?? 0);
$playtimeText = intdiv($playtime, 3600) . ' ч '
    . intdiv($playtime % 3600, 60) . ' мин';
?>

<div class="container ht-section">
    <div class="section-title">
        <div>
            <h2><?= Html::encode($user->username) ?></h2>
            <p class="muted mb-0">
                Публичный игровой профиль HiveTech.
            </p>
        </div>

        <?php if((bool)$settings->public_ranking && $rank !== null): ?>
            <span class="badge text-bg-success fs-6">
                #<?= (int)$rank ?> в рейтинге
            </span>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <?php if((bool)$settings->public_achievements): ?>
            <div class="col-md-4">
                <div class="ht-card h-100">
                    <div class="muted">Достижения</div>
                    <div class="metric">
                        <?= (int)$summary['unlocked'] ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="ht-card h-100">
                    <div class="muted">Achievement score</div>
                    <div class="metric">
                        <?= number_format((int)$summary['score'], 0, ',', ' ') ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if((bool)$settings->public_playtime): ?>
            <div class="col-md-4">
                <div class="ht-card h-100">
                    <div class="muted">Игровое время</div>
                    <div class="metric"><?= Html::encode($playtimeText) ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if((bool)$settings->public_achievements): ?>
        <div class="ht-card">
            <h3 class="mb-3">Полученные достижения</h3>

            <?php if(!$unlocks): ?>
                <div class="muted">Пока ничего не разблокировано.</div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach($unlocks as $unlock): ?>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex justify-content-between gap-3">
                                    <strong>
                                        <?= Html::encode(
                                            $unlock->achievement?->title
                                            ?: ('#' . $unlock->achievement_id)
                                        ) ?>
                                    </strong>
                                    <span class="badge text-bg-secondary">
                                        +<?= (int)$unlock->score_awarded ?>
                                    </span>
                                </div>

                                <div class="muted small mt-2">
                                    <?= Html::encode((string)$unlock->unlocked_at) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
