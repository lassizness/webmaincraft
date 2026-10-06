<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class Wallet extends ActiveRecord
{
    public static function tableName(): string { return '{{%wallets}}'; }
    public function rules(): array { return [[['user_id'],'required'],[['user_id'],'integer'],[['balance'],'number','min'=>0],['user_id','unique']]; }
    public function getUser(){ return $this->hasOne(User::class,['id'=>'user_id']); }
}
