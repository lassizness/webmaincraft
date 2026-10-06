<?php

declare(strict_types=1);namespace backend\controllers;use backend\components\AdminController;use common\models\{User,SupportTicket,PaymentOrder,ShopPurchase};
final class DashboardController extends AdminController { public function actionIndex():string{return $this->render('index',['users'=>User::find()->count(),'pendingUsers'=>User::find()->where(['status'=>User::STATUS_PENDING])->count(),'openTickets'=>SupportTicket::find()->where(['<>','status',SupportTicket::STATUS_CLOSED])->count(),'newPayments'=>PaymentOrder::find()->where(['status'=>PaymentOrder::STATUS_NEW])->count(),'purchases'=>ShopPurchase::find()->count()]);}}
