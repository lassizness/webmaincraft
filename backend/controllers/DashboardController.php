<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\{
    PaymentOrder,
    ShopPurchase,
    SupportTicket,
    User
};

final class DashboardController extends AdminController
{
    public function actionIndex(): string
    {
        return $this->render('index', [
            'users' => User::find()->count(),
            'unverifiedUsers' => User::find()
                ->where(['email_verified' => 0])
                ->count(),
            'openTickets' => SupportTicket::find()
                ->where(['<>', 'status', SupportTicket::STATUS_CLOSED])
                ->count(),
            'newPayments' => PaymentOrder::find()
                ->where(['status' => PaymentOrder::STATUS_NEW])
                ->count(),
            'purchases' => ShopPurchase::find()->count(),
        ]);
    }
}
