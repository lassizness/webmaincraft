<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class WalletTransaction extends ActiveRecord
{
    public static function tableName(): string { return '{{%wallet_transactions}}'; }
    public function getUser(){ return $this->hasOne(User::class,['id'=>'user_id']); }
}
