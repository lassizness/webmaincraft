<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\{
    ShopCategory,
    ShopProduct
};
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

final class ShopController extends AdminController
{
    protected string $permission =
        'manageShop';

    public function actionIndex(): string
    {
        return $this->render(
            'index',
            [
                'categories' =>
                    ShopCategory::find()
                        ->orderBy(
                            [
                                'sort' =>
                                    SORT_ASC,
                            ]
                        )
                        ->all(),
                'dataProvider' =>
                    new ActiveDataProvider(
                        [
                            'query' =>
                                ShopProduct::find()
                                    ->with(
                                        [
                                            'category',
                                            'privilegeGroup',
                                        ]
                                    )
                                    ->orderBy(
                                        [
                                            'sort' =>
                                                SORT_ASC,
                                            'id' =>
                                                SORT_DESC,
                                        ]
                                    ),
                        ]
                    ),
            ]
        );
    }

    public function actionCategory(
        ?int $id = null
    )
    {
        $model =
            $id
                ? ShopCategory::findOne(
                    $id
                )
                : new ShopCategory(
                    [
                        'enabled' => 1,
                        'sort' => 100,
                    ]
                );

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        if(
            $model->load(
                Yii::$app->request->post()
            )
            && $model->save()
        )
        {
            return $this->redirect(
                [
                    'index',
                ]
            );
        }

        return $this->render(
            'category-form',
            [
                'model' =>
                    $model,
            ]
        );
    }

    public function actionProduct(
        ?int $id = null
    )
    {
        $model =
            $id
                ? ShopProduct::findOne(
                    $id
                )
                : new ShopProduct(
                    [
                        'enabled' => 1,
                        'sort' => 100,
                        'product_type' =>
                            ShopProduct::TYPE_ITEM,
                        'delivery_type' =>
                            'none',
                    ]
                );

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        if(
            $model->load(
                Yii::$app->request->post()
            )
            && $model->save()
        )
        {
            return $this->redirect(
                [
                    'index',
                ]
            );
        }

        return $this->render(
            'product-form',
            [
                'model' =>
                    $model,
            ]
        );
    }
}
