<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class PromoCode extends ActiveRecord
{
    public static function tableName(): string { return '{{%promo_codes}}'; }
    public function rules(): array { return [[['code','type','value'],'required'],[['value'],'number','min'=>0],['type','in','range'=>['wallet','product']],[[ 'product_id','max_uses','used_count','enabled'],'integer'],[['expires_at'],'safe'],[['code'],'string','max'=>64],['code','unique']]; }
}
