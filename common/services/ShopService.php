<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    DeliveryQueue,
    ShopProduct,
    ShopPurchase
};
use Yii;

final class ShopService
{
    public function buy(
        int $userId,
        ShopProduct $product
    ): ShopPurchase
    {
        if(!$product->enabled)
        {
            throw new \DomainException(
                'Товар недоступен'
            );
        }

        $transaction =
            Yii::$app->db
                ->beginTransaction();

        try
        {
            $purchase =
                new ShopPurchase(
                    [
                        'user_id' =>
                            $userId,
                        'product_id' =>
                            $product->id,
                        'price' =>
                            $product->price,
                        'status' =>
                            'paid',
                        'created_at' =>
                            date(
                                'Y-m-d H:i:s'
                            ),
                    ]
                );

            if(!$purchase->save())
            {
                throw new \RuntimeException(
                    'Purchase failed'
                );
            }

            (
                new WalletService()
            )->debit(
                $userId,
                (float)$product->price,
                'shop',
                'Покупка: '
                    . $product->name,
                'purchase',
                (int)$purchase->id
            );

            if(
                $product->product_type
                === ShopProduct::TYPE_PRIVILEGE
            )
            {
                (
                    new PrivilegeService()
                )->purchase(
                    $userId,
                    $product,
                    $purchase
                );
            }
            elseif(
                $product->delivery_type
                !== 'none'
            )
            {
                $queue =
                    new DeliveryQueue(
                        [
                            'purchase_id' =>
                                $purchase->id,
                            'user_id' =>
                                $userId,
                            'server_id' =>
                                $product->server_id,
                            'delivery_type' =>
                                $product->delivery_type,
                            'payload' =>
                                $product->delivery_payload,
                            'status' =>
                                'pending',
                            'attempts' =>
                                0,
                            'created_at' =>
                                date(
                                    'Y-m-d H:i:s'
                                ),
                        ]
                    );

                $queue->save(
                    false
                );
            }

            $transaction->commit();

            return $purchase;
        }
        catch(\Throwable $e)
        {
            if($transaction->isActive)
            {
                $transaction->rollBack();
            }

            throw $e;
        }
    }
}
