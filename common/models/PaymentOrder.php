<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class PaymentOrder extends ActiveRecord
{
    public const STATUS_NEW='new'; public const STATUS_PAID='paid'; public const STATUS_CANCELLED='cancelled'; public const STATUS_FAILED='failed';
    public static function tableName(): string { return '{{%payment_orders}}'; }
    public function rules(): array { return [[['user_id','amount'],'required'],[['user_id'],'integer'],[['amount'],'number','min'=>1],['status','in','range'=>[self::STATUS_NEW,self::STATUS_PAID,self::STATUS_CANCELLED,self::STATUS_FAILED]],['provider','string','max'=>32]]; }
}
