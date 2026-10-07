<?php

declare(strict_types=1);

namespace console\controllers;

use common\services\{
    PrivilegeNotificationService,
    PrivilegeService
};
use yii\console\Controller;

final class PrivilegeController extends Controller
{
    public function actionExpire(int $limit = 100): int
    {
        $count = (new PrivilegeService())->expireDue($limit);
        $this->stdout("Expired privileges: {$count}\n");

        return 0;
    }

    public function actionNotify(int $limit = 200): int
    {
        $count = (new PrivilegeNotificationService())->sendDue($limit);
        $this->stdout("Privilege notifications sent: {$count}\n");

        return 0;
    }
}
