<?php

declare(strict_types=1);

use common\models\{User, VoteService};
use yii\helpers\{Html, Url};

/** @var VoteService[] $services */
/** @var User|null $player */
/** @var string $usernameValue */
/** @var string|null $usernameError */
/** @var array<int, array{available: bool, nextRewardAt: ?int}> $statuses */

$this->title = Yii::t('ui', 'Голосование');

$formatReward = static function (float $amount): string {
    $value = number_format($amount, 2, '.', '');
    return rtrim(rtrim($value, '0'), '.');
};
?>

<div class="container ht-section">
    <div class="section-title">
        <div>
            <h2><?= Yii::t('ui', 'Голосование') ?></h2>
            <p class="muted mb-0">
                <?= Yii::t('ui', 'Поддержи HiveGrid на мониторингах и получай кварки за голос.') ?>
            </p>
        </div>
    </div>

    <?php if (Yii::$app->user->isGuest): ?>
        <div class="ht-card mb-4">
            <form method="get" action="<?= Url::to(['/vote/index']) ?>" class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label" for="vote-username">
                        <?= Yii::t('ui', 'Minecraft-ник') ?>
                    </label>
                    <input
                        id="vote-username"
                        class="form-control<?= $usernameError !== null ? ' is-invalid' : '' ?>"
                        type="text"
                        name="username"
                        value="<?= Html::encode($usernameValue) ?>"
                        pattern="[A-Za-z0-9_]{3,16}"
                        maxlength="16"
                        autocomplete="off"
                        placeholder="Steve"
                    >
                    <?php if ($usernameError !== null): ?>
                        <div class="invalid-feedback">
                            <?= Html::encode($usernameError) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary w-100" type="submit">
                        <?= Yii::t('ui', 'Продолжить') ?>
                    </button>
                </div>
            </form>
        </div>
    <?php elseif ($player !== null): ?>
        <div class="alert alert-info">
            <?= Yii::t('ui', 'Голос будет засчитан игроку {username}.', [
                'username' => Html::encode((string)$player->username),
            ]) ?>
        </div>
    <?php endif; ?>

    <?php if (!$services): ?>
        <div class="ht-card">
            <p class="muted mb-0">
                <?= Yii::t('ui', 'Сервисы голосования пока не настроены.') ?>
            </p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($services as $service): ?>
                <?php
                    $status = $statuses[(int)$service->id] ?? null;
                    $available = $status === null || (bool)$status['available'];
                    $reward = $formatReward((float)$service->reward_amount);
                ?>
                <div class="col-md-6 col-xl-4" id="vote-service-<?= Html::encode((string)$service->code) ?>">
                    <div class="ht-card h-100 d-flex flex-column">
                        <div class="eyebrow"><?= Html::encode((string)$service->code) ?></div>
                        <h3><?= Html::encode((string)$service->name) ?></h3>

                        <?php if (trim((string)$service->banner_url) !== ''): ?>
                            <img
                                class="mb-3"
                                src="<?= Html::encode((string)$service->banner_url) ?>"
                                alt="<?= Html::encode((string)$service->name) ?>"
                                style="max-width:100%;height:auto"
                            >
                        <?php endif; ?>

                        <p class="mb-2">
                            <strong>+<?= Html::encode($reward) ?> <?= Yii::t('ui', 'кварков') ?></strong>
                        </p>

                        <?php if ($player !== null && $status !== null && !$available): ?>
                            <p class="muted small">
                                <?= Yii::t('ui', 'Следующая награда будет доступна {time}.', [
                                    'time' => Yii::$app->formatter->asDatetime((int)$status['nextRewardAt'], 'short'),
                                ]) ?>
                            </p>
                        <?php elseif ($player !== null): ?>
                            <p class="text-success small">
                                <?= Yii::t('ui', 'Награда доступна сейчас.') ?>
                            </p>
                        <?php else: ?>
                            <p class="muted small">
                                <?= Yii::t('ui', 'Укажи Minecraft-ник, чтобы проголосовать.') ?>
                            </p>
                        <?php endif; ?>

                        <div class="mt-auto pt-2">
                            <?php if ($player === null): ?>
                                <button class="btn btn-primary w-100" type="button" disabled>
                                    <?= Yii::t('ui', 'Голосовать') ?>
                                </button>
                            <?php elseif (!$available): ?>
                                <button class="btn btn-outline-secondary w-100" type="button" disabled>
                                    <?= Yii::t('ui', 'Награда уже получена') ?>
                                </button>
                            <?php else: ?>
                                <a
                                    class="btn btn-primary w-100"
                                    href="<?= Url::to([
                                        '/vote/go',
                                        'service' => (string)$service->code,
                                        'username' => (string)$player->username,
                                    ]) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <?= Yii::t('ui', 'Голосовать') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
