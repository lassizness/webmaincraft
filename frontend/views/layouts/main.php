<?php

use common\components\LanguageBootstrap;
use common\models\Setting;
use common\services\{
    SeoService,
    SiteCounterService
};
use frontend\assets\AppAsset;
use yii\helpers\{Html, Url};

AppAsset::register($this);

$app = Yii::$app->params['appName'];
$lang = Yii::$app->language;

$seoIndexable = SeoService::isIndexable();
$seoDescription = SeoService::description(
    $this->params['seoDescription'] ?? null
);
$seoTitle = $this->title
    ? $this->title . ' — ' . $app
    : $app;
$seoLanguages = $this->params['seoLanguages']
    ?? null;

$seoCanonicalLanguage = $lang;

if(
    is_array($seoLanguages)
    && $seoLanguages
    && !in_array(
        $seoCanonicalLanguage,
        $seoLanguages,
        true
    )
)
{
    $seoCanonicalLanguage =
        in_array('ru', $seoLanguages, true)
            ? 'ru'
            : (string)reset($seoLanguages);
}

$seoCanonical = $seoIndexable
    ? SeoService::canonicalUrl(
        $seoCanonicalLanguage
    )
    : null;

$seoAlternates = $seoIndexable
    ? SeoService::alternateUrls($seoLanguages)
    : [];
$seoImage = trim((string)(
    $this->params['seoImage']
    ?? SeoService::logoUrl()
));
$seoType = trim((string)(
    $this->params['seoType']
    ?? 'website'
));
$seoPublishedTime = trim((string)(
    $this->params['seoPublishedTime']
    ?? ''
));

if(!$seoIndexable)
{
    Yii::$app->response->headers->set(
        'X-Robots-Tag',
        'noindex, nofollow, noarchive'
    );
}
?>
<?php $this->beginPage() ?>
<!doctype html>
<html lang="<?= Html::encode($lang) ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#04152d">

    <?php $this->registerCsrfMetaTags() ?>

    <title><?= Html::encode($seoTitle) ?></title>

    <meta
        name="description"
        content="<?= Html::encode($seoDescription) ?>"
    >
    <meta
        name="robots"
        content="<?= $seoIndexable
            ? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'
            : 'noindex, nofollow, noarchive'
        ?>"
    >

    <?php foreach(SeoService::verificationMeta() as $name => $value): ?>
        <meta
            name="<?= Html::encode($name) ?>"
            content="<?= Html::encode($value) ?>"
        >
    <?php endforeach; ?>

    <?php if($seoCanonical !== null): ?>
        <link
            rel="canonical"
            href="<?= Html::encode($seoCanonical) ?>"
        >

        <?php foreach($seoAlternates as $language => $url): ?>
            <link
                rel="alternate"
                hreflang="<?= Html::encode($language) ?>"
                href="<?= Html::encode($url) ?>"
            >
        <?php endforeach; ?>
    <?php endif; ?>

    <meta property="og:site_name" content="<?= Html::encode($app) ?>">
    <meta property="og:type" content="<?= Html::encode($seoType) ?>">
    <meta property="og:title" content="<?= Html::encode($seoTitle) ?>">
    <meta property="og:description" content="<?= Html::encode($seoDescription) ?>">
    <meta property="og:locale" content="<?= Html::encode(SeoService::locale()) ?>">
    <meta property="og:image" content="<?= Html::encode($seoImage) ?>">

    <?php if($seoCanonical !== null): ?>
        <meta property="og:url" content="<?= Html::encode($seoCanonical) ?>">
    <?php endif; ?>

    <?php foreach(array_keys($seoAlternates) as $language): ?>
        <?php if($language !== 'x-default' && $language !== $lang): ?>
            <meta
                property="og:locale:alternate"
                content="<?= Html::encode(
                    SeoService::locale($language)
                ) ?>"
            >
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if($seoType === 'article' && $seoPublishedTime !== ''): ?>
        <meta
            property="article:published_time"
            content="<?= Html::encode($seoPublishedTime) ?>"
        >
    <?php endif; ?>

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= Html::encode($seoTitle) ?>">
    <meta name="twitter:description" content="<?= Html::encode($seoDescription) ?>">
    <meta name="twitter:image" content="<?= Html::encode($seoImage) ?>">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="192x192" href="/favicon-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">

    <?php if($seoIndexable): ?>
        <?php
        $structuredData = array_merge(
            SeoService::websiteStructuredData(),
            (array)($this->params['seoStructuredData'] ?? [])
        );
        ?>
        <?php foreach($structuredData as $schema): ?>
            <script type="application/ld+json"><?= json_encode(
                $schema,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
            ) ?></script>
        <?php endforeach; ?>
    <?php endif; ?>

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

            <?php if((string)Setting::value('ratings.public_enabled', '0') === '1'): ?>
                <a href="<?= Url::to(['/ratings/index']) ?>">
                    <?= Yii::t('app', 'Рейтинги') ?>
                </a>
            <?php endif; ?>

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
