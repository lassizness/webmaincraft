<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class DeliveryQueue extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%delivery_queue}}';
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getServer()
    {
        return $this->hasOne(Server::class, ['id' => 'server_id']);
    }

    public function getPurchase()
    {
        return $this->hasOne(ShopPurchase::class, ['id' => 'purchase_id']);
    }
}
