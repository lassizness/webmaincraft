<?php

use yii\helpers\{Html, Url};

?>

<section class="ht-hero">
    <div class="container">
        <div class="hero-card">

            <div class="eyebrow">
                Minecraft • HiveTech Network
            </div>

            <h1>
                <?= Yii::t(
                    'app',
                    'Сервер, который развивается вместе с игроками.'
                ) ?>
            </h1>

            <p>
                <?= Yii::t(
                    'app',
                    'Один аккаунт для сайта и HiveTech Launcher, собственные сборки, скины, поддержка и прозрачная экономика без pay-to-win.'
                ) ?>
            </p>

            <div class="d-flex gap-2 mt-4">

                <?php if(Yii::$app->user->isGuest): ?>

                    <a
                        class="btn btn-primary btn-lg"
                        href="<?= Url::to(['/auth/register']) ?>"
                    >
                        <?= Yii::t('app', 'Создать аккаунт') ?>
                    </a>

                    <a
                        class="btn btn-outline-light btn-lg"
                        href="<?= Url::to(['/auth/login']) ?>"
                    >
                        <?= Yii::t('app', 'Войти') ?>
                    </a>

                <?php else: ?>

                    <a
                        class="btn btn-primary btn-lg"
                        href="<?= Url::to(['/account/index']) ?>"
                    >
                        <?= Yii::t('app', 'Открыть кабинет') ?>
                    </a>

                <?php endif; ?>

            </div>

        </div>
    </div>
</section>

<?php if (!empty($voteServices)): ?>
<section class="ht-section">
    <div class="container">
        <div class="section-title">
            <div>
                <h2><?= Yii::t('ui', 'Поддержать проект') ?></h2>
                <p class="muted mb-0"><?= Yii::t('ui', 'Голосуй за HiveGrid в рейтингах и получай кварки.') ?></p>
            </div>
            <a href="<?= Url::to(['/vote/index']) ?>"><?= Yii::t('ui', 'Все рейтинги') ?></a>
        </div>

        <div class="d-flex flex-wrap gap-3 align-items-center">
            <?php foreach ($voteServices as $voteService): ?>
                <?php
                    $voteHref = str_contains((string)$voteService->vote_url_template, '{username}')
                        ? Url::to(['/vote/index']) . '#vote-service-' . rawurlencode((string)$voteService->code)
                        : (string)$voteService->vote_url_template;
                ?>
                <a href="<?= Html::encode($voteHref) ?>" target="_blank" rel="noopener noreferrer"
                   title="<?= Html::encode((string)$voteService->name) ?>">
                    <img
                        src="<?= Html::encode((string)$voteService->banner_url) ?>"
                        alt="<?= Html::encode((string)$voteService->name) ?>"
                        style="max-width:100%;height:auto;display:block"
                    >
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<section class="ht-section">
    <div class="container">

        <div class="section-title">

            <h2>
                <?= Yii::t('app', 'Серверы') ?>
            </h2>

            <a href="<?= Url::to(['/server/index']) ?>">
                <?= Yii::t('app', 'Все серверы') ?>
            </a>

        </div>

        <div class="row g-3">

            <?php foreach($servers as $server): ?>

                <div class="col-md-4">
                    <div class="ht-card">

                        <div class="eyebrow">
                            <?= Html::encode($server->code) ?>
                        </div>

                        <h3>
                            <?= Html::encode($server->name) ?>
                        </h3>

                        <p class="muted">
                            <?= Html::encode(
                                $server->description
                                ?: $server->host . ':' . $server->port
                            ) ?>
                        </p>

                    </div>
                </div>

            <?php endforeach; ?>

            <?php if(!$servers): ?>

                <p class="muted">
                    <?= Yii::t(
                        'app',
                        'Серверы появятся после настройки игрового контура.'
                    ) ?>
                </p>

            <?php endif; ?>

        </div>

    </div>
</section>


<section class="ht-section">
    <div class="container">

        <div class="section-title">

            <h2>
                <?= Yii::t('app', 'Новости') ?>
            </h2>

            <a href="<?= Url::to(['/news/index']) ?>">
                <?= Yii::t('app', 'Архив') ?>
            </a>

        </div>

        <div class="news-grid">

            <?php foreach($news as $item): ?>

                <article class="news-card">

                    <div class="muted small">
                        <?= Html::encode(
                            $item->published_at ?: $item->created_at
                        ) ?>
                    </div>

                    <h3>
                        <a href="<?= Url::to([
                            '/news/view',
                            'slug' => $item->slug,
                        ]) ?>">
                            <?= Html::encode($item->title) ?>
                        </a>
                    </h3>

                    <p class="muted">
                        <?= Html::encode(
                            $item->excerpt
                            ?: mb_substr(
                                strip_tags($item->body),
                                0,
                                150
                            ) . '…'
                        ) ?>
                    </p>

                </article>

            <?php endforeach; ?>

            <?php if(!$news): ?>

                <div class="news-card">

                    <h3>
                        <?= Yii::t(
                            'app',
                            'HiveTech готовится к запуску'
                        ) ?>
                    </h3>

                    <p class="muted">
                        <?= Yii::t(
                            'app',
                            'Новости проекта будут публиковаться здесь.'
                        ) ?>
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>
</section>
