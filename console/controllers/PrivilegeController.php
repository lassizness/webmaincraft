<?php

declare(strict_types=1);

namespace console\controllers;

use common\services\PrivilegeService;
use yii\console\Controller;

final class PrivilegeController extends Controller
{
    public function actionExpire(
        int $limit = 100
    ): int
    {
        $count =
            (
                new PrivilegeService()
            )->expireDue(
                $limit
            );

        $this->stdout(
            "Expired privileges: {$count}\n"
        );

        return 0;
    }
}
