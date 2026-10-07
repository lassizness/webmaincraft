<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\{
    ShopProduct,
    UserPrivilege
};
use common\services\ShopService;
use frontend\components\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

final class PrivilegeController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'renew'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $privileges = UserPrivilege::find()
            ->with(['group', 'server'])
            ->where(['user_id' => Yii::$app->user->id])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        $renewalProducts = [];

        foreach($privileges as $privilege)
        {
            $renewalProducts[(int)$privilege->id] = ShopProduct::find()
                ->where([
                    'enabled' => 1,
                    'product_type' => ShopProduct::TYPE_PRIVILEGE,
                    'server_id' => $privilege->server_id,
                    'privilege_group_id' => $privilege->privilege_group_id,
                ])
                ->andWhere(['>', 'duration_days', 0])
                ->orderBy(['duration_days' => SORT_ASC, 'price' => SORT_ASC])
                ->all();
        }

        return $this->render('index', [
            'privileges' => $privileges,
            'renewalProducts' => $renewalProducts,
        ]);
    }

    public function actionRenew(int $productId)
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $product = ShopProduct::findOne([
            'id' => $productId,
            'enabled' => 1,
            'product_type' => ShopProduct::TYPE_PRIVILEGE,
        ]);

        if(!$product)
        {
            throw new NotFoundHttpException();
        }

        $privilege = UserPrivilege::findOne([
            'user_id' => Yii::$app->user->id,
            'server_id' => $product->server_id,
            'privilege_group_id' => $product->privilege_group_id,
        ]);

        if(!$privilege)
        {
            throw new NotFoundHttpException();
        }

        try
        {
            (new ShopService())->buy((int)Yii::$app->user->id, $product);

            Yii::$app->session->setFlash(
                'success',
                Yii::t('ui', 'Статус продлён. Оставшийся срок сохранён.')
            );
        }
        catch(\Throwable $e)
        {
            Yii::$app->session->setFlash(
                'error',
                Yii::t('ui', $e->getMessage())
            );
        }

        return $this->redirect(['index']);
    }
}
