<?php

use common\services\ContentTranslationService;
use yii\helpers\{Html, Url};
use yii\widgets\ListView;

$this->title = Yii::t('ui', 'Новости');

?>

<div class="container ht-section">

    <div class="section-title">
        <h2>
            <?= Yii::t('ui', 'Новости HiveTech') ?>
        </h2>
    </div>

    <?= ListView::widget([
        'dataProvider' => $dataProvider,

        'itemView' => static function($model)
        {
            ContentTranslationService::applyNews($model);

            $date = $model->published_at
                ?: $model->created_at;

            $excerpt = $model->excerpt
                ?: mb_substr(
                    strip_tags($model->body),
                    0,
                    220
                ) . '…';

            return
                '<article class="news-card mb-3">'
                . '<div class="muted small">'
                . Html::encode($date)
                . '</div>'
                . '<h3><a href="'
                . Url::to([
                    '/news/view',
                    'slug' => $model->slug,
                ])
                . '">'
                . Html::encode($model->title)
                . '</a></h3>'
                . '<p class="muted">'
                . Html::encode($excerpt)
                . '</p>'
                . '</article>';
        },

        'summary' => '',
        'emptyText' => '',
    ]) ?>

</div>
