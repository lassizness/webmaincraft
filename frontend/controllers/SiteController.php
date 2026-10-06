<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\{
    News,
    Server,
    Slide,
    VoteService
};
use common\services\ContentTranslationService;
use frontend\components\Controller;

final class SiteController extends Controller
{
    public function actions(): array
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    public function actionIndex(): string
    {
        $news = News::find()
            ->where(['published' => 1])
            ->andWhere([
                'or',
                ['published_at' => null],
                ['<=', 'published_at', date('Y-m-d H:i:s')],
            ])
            ->orderBy([
                'published_at' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->limit(6)
            ->all();

        foreach($news as $item)
        {
            ContentTranslationService::applyNews($item);
        }

        return $this->render('index', [
            'news' => $news,

            'slides' => Slide::find()
                ->where(['enabled' => 1])
                ->orderBy([
                    'sort' => SORT_ASC,
                    'id' => SORT_ASC,
                ])
                ->all(),

            'servers' => Server::find()
                ->where(['enabled' => 1])
                ->orderBy(['sort' => SORT_ASC])
                ->all(),

            'voteServices' => VoteService::find()
                ->where(['enabled' => 1, 'show_on_home' => 1])
                ->andWhere(['not', ['banner_url' => null]])
                ->andWhere(['<>', 'banner_url', ''])
                ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
                ->all(),
        ]);
    }
}
