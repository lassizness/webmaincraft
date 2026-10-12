<?php

use common\services\SeoService;
use yii\helpers\Html;

$this->title = $model->title;
$this->params['seoDescription'] =
    $model->excerpt
    ?: SeoService::plainText(
        (string)$model->body,
        220
    );
$this->params['seoLanguages'] =
    SeoService::contentLanguages(
        'news',
        (int)$model->id
    );
$this->params['seoType'] = 'article';

$publishedAt = $model->published_at
    ?: $model->created_at;

if($publishedAt)
{
    $publishedTimestamp = strtotime(
        (string)$publishedAt
    );

    if($publishedTimestamp !== false)
    {
        $this->params['seoPublishedTime'] =
            date(DATE_ATOM, $publishedTimestamp);
    }
}

$this->params['seoStructuredData'][] = [
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => (string)$model->title,
    'description' =>
        (string)$this->params['seoDescription'],
    'datePublished' =>
        $this->params['seoPublishedTime']
        ?? null,
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => SeoService::canonicalUrl(),
    ],
    'image' => [
        SeoService::logoUrl(),
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => (string)Yii::$app->params['appName'],
        'logo' => [
            '@type' => 'ImageObject',
            'url' => SeoService::logoUrl(),
        ],
    ],
];

?>

<div class="container ht-section">
    <article class="ht-card">
        <div class="muted mb-2">
            <?= Html::encode($publishedAt) ?>
        </div>

        <h1><?= Html::encode($model->title) ?></h1>

        <div class="mt-4 content-body">
            <?= Yii::$app->formatter->asHtml($model->body) ?>
        </div>
    </article>
</div>
