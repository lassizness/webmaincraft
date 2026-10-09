<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\Setting;
use common\services\AchievementProfileService;

final class RatingController extends AdminController
{
    protected string $permission = 'manageRatings';

    public function actionIndex(): string
    {
        return $this->render('index', [
            'rows' => (new AchievementProfileService())
                ->leaderboard(200, false),
            'publicEnabled' =>
                (string)Setting::value(
                    'ratings.public_enabled',
                    '0'
                ) === '1',
        ]);
    }
}
