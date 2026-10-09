<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\{
    Setting,
    User
};
use common\services\AchievementProfileService;
use frontend\components\Controller;
use yii\web\NotFoundHttpException;

final class RatingsController extends Controller
{
    public function actionIndex(): string
    {
        $this->ensureEnabled();

        return $this->render('index', [
            'rows' => (new AchievementProfileService())
                ->leaderboard(100),
        ]);
    }

    public function actionPlayer(string $username): string
    {
        $this->ensureEnabled();

        $user = User::findActiveByUsername($username);

        if(!$user)
        {
            throw new NotFoundHttpException();
        }

        $service = new AchievementProfileService();
        $settings = $service->profileSettings((int)$user->id);

        if(
            !(bool)$settings->public_achievements
            && !(bool)$settings->public_playtime
            && !(bool)$settings->public_ranking
        )
        {
            throw new NotFoundHttpException();
        }

        return $this->render('player', [
            'user' => $user,
            'settings' => $settings,
            'catalog' => (bool)$settings->public_achievements
                ? $service->catalog()
                : [],
            'unlocks' => (bool)$settings->public_achievements
                ? $service->unlocksForUser((int)$user->id)
                : [],
            'stats' => (bool)$settings->public_playtime
                ? $service->statsForUser((int)$user->id)
                : [],
            'summary' => $service->summary((int)$user->id),
            'rank' => (bool)$settings->public_ranking
                ? $service->rankForUser((int)$user->id)
                : null,
        ]);
    }

    private function ensureEnabled(): void
    {
        if(
            (string)Setting::value(
                'ratings.public_enabled',
                '0'
            ) !== '1'
        )
        {
            throw new NotFoundHttpException();
        }
    }
}
