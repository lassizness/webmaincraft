<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class ShopPurchase extends ActiveRecord
{
    public static function tableName(): string { return '{{%shop_purchases}}'; }
    public function getProduct(){ return $this->hasOne(ShopProduct::class,['id'=>'product_id']); }
}
