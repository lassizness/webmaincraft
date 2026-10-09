<?php

declare(strict_types=1);

namespace console\controllers;

use common\services\ModerationService;
use yii\console\Controller;

final class ModerationController extends Controller
{
    public function actionExpire(
        int $limit = 200
    ): int
    {
        $count =
            (new ModerationService())
                ->expireDue($limit);

        $this->stdout(
            "Expired moderation actions: {$count}\n"
        );

        return 0;
    }
}
