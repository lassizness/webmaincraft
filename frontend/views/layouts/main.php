<?php

use common\components\LanguageBootstrap;
use common\services\SiteCounterService;
use frontend\assets\AppAsset;
use yii\helpers\{Html, Url};

AppAsset::register($this);

$app = Yii::$app->params['appName'];
$lang = Yii::$app->language;
?>
<?php $this->beginPage() ?>
<!doctype html>
<html lang="<?= Html::encode($lang) ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <?php $this->registerCsrfMetaTags() ?>

    <title>
        <?= Html::encode(
            $this->title
                ? $this->title . ' — ' . $app
                : $app
        ) ?>
    </title>

    <?php $this->head() ?>

    <?= SiteCounterService::render('head') ?>
</head>

<body>
<?php $this->beginBody() ?>

<?= SiteCounterService::render('body_start') ?>

<header class="ht-header">
    <div class="container ht-nav">

        <a class="ht-brand" href="<?= Url::to(['/']) ?>">
            <span class="ht-mark">H</span>
            <span>HiveTech</span>
        </a>

        <nav>
            <a href="<?= Url::to(['/news/index']) ?>">
                <?= Yii::t('app', 'Новости') ?>
            </a>

            <a href="<?= Url::to(['/server/index']) ?>">
                <?= Yii::t('app', 'Серверы') ?>
            </a>

            <a href="<?= Url::to(['/page/view', 'slug' => 'play']) ?>">
                <?= Yii::t('app', 'Начать играть') ?>
            </a>

            <a href="<?= Url::to(['/page/view', 'slug' => 'guides']) ?>">
                <?= Yii::t('app', 'Гайды') ?>
            </a>

            <a href="<?= Url::to(['/shop/index']) ?>">
                <?= Yii::t('app', 'Магазин') ?>
            </a>

            <a href="<?= Url::to(['/vote/index']) ?>">
                <?= Yii::t('app', 'Голосовать') ?>
            </a>

            <a href="<?= Url::to(['/page/view', 'slug' => 'rules']) ?>">
                <?= Yii::t('app', 'Правила') ?>
            </a>

            <a href="<?= Url::to(['/support/index']) ?>">
                <?= Yii::t('app', 'Поддержка') ?>
            </a>
        </nav>

        <div class="ht-auth">

            <div class="btn-group btn-group-sm me-2">
                <?php foreach(LanguageBootstrap::LANGUAGES as $code => $name): ?>

                    <a
                        href="<?= Url::to([
                            '/language/switch',
                            'lang' => $code,
                            'return' => Yii::$app->request->url,
                        ]) ?>"
                        class="btn <?= $lang === $code
                            ? 'btn-light'
                            : 'btn-outline-light'
                        ?>"
                        title="<?= Html::encode($name) ?>"
                    >
                        <?= strtoupper($code) ?>
                    </a>

                <?php endforeach; ?>
            </div>

            <?php if(Yii::$app->user->isGuest): ?>

                <a
                    class="btn btn-outline-light btn-sm"
                    href="<?= Url::to(['/auth/login']) ?>"
                >
                    <?= Yii::t('app', 'Войти') ?>
                </a>

                <a
                    class="btn btn-primary btn-sm"
                    href="<?= Url::to(['/auth/register']) ?>"
                >
                    <?= Yii::t('app', 'Регистрация') ?>
                </a>

            <?php else: ?>

                <a
                    href="<?= Url::to(['/account/index']) ?>"
                    class="ht-user"
                >
                    <?= Html::encode(Yii::$app->user->identity->username) ?>
                </a>

                <?= Html::beginForm(
                    ['/auth/logout'],
                    'post',
                    ['class' => 'd-inline']
                ) ?>

                <?= Html::submitButton(
                    Yii::t('app', 'Выйти'),
                    ['class' => 'btn btn-outline-light btn-sm']
                ) ?>

                <?= Html::endForm() ?>

            <?php endif; ?>

        </div>
    </div>
</header>

<main>

<?php foreach(['success', 'error', 'warning', 'info'] as $type): ?>

    <?php if(Yii::$app->session->hasFlash($type)): ?>

        <div class="container mt-3">
            <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?>">
                <?= Html::encode(Yii::$app->session->getFlash($type)) ?>
            </div>
        </div>

    <?php endif; ?>

<?php endforeach; ?>

<?= $content ?>

</main>

<footer class="ht-footer">
    <div class="container d-flex justify-content-between flex-wrap gap-2">

        <span>
            © <?= date('Y') ?> HiveTech
        </span>

        <span>
            <?= Yii::t(
                'app',
                'Единый аккаунт сайта и лаунчера'
            ) ?>
        </span>

    </div>
</footer>

<?= SiteCounterService::render('body_end') ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
