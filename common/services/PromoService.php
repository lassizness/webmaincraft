<?php

declare(strict_types=1);
namespace common\services;
use common\models\{PromoCode,PromoCodeUsage,ShopProduct,DeliveryQueue};use Yii;
final class PromoService
{
    public function redeem(int $userId,string $code): string
    {
        $p=PromoCode::findOne(['code'=>mb_strtoupper(trim($code)),'enabled'=>1]);if(!$p)throw new \DomainException('Промокод не найден');if($p->expires_at&&strtotime($p->expires_at)<time())throw new \DomainException('Промокод истёк');if($p->max_uses&&$p->used_count>=$p->max_uses)throw new \DomainException('Лимит использований исчерпан');if(PromoCodeUsage::find()->where(['promo_code_id'=>$p->id,'user_id'=>$userId])->exists())throw new \DomainException('Промокод уже использован');
        $tx=Yii::$app->db->beginTransaction();try{if($p->type==='wallet'){(new WalletService())->credit($userId,(float)$p->value,'promo','Промокод '.$p->code,'promo',(int)$p->id);$msg='Баланс пополнен';}else{$product=ShopProduct::findOne($p->product_id);if(!$product)throw new \DomainException('Товар промокода недоступен');$q=new DeliveryQueue(['user_id'=>$userId,'server_id'=>$product->server_id,'delivery_type'=>$product->delivery_type,'payload'=>$product->delivery_payload,'status'=>'pending','attempts'=>0,'created_at'=>date('Y-m-d H:i:s')]);$q->save(false);$msg='Награда поставлена в очередь';}$u=new PromoCodeUsage(['promo_code_id'=>$p->id,'user_id'=>$userId,'used_at'=>date('Y-m-d H:i:s')]);$u->save(false);$p->used_count++;$p->save(false,['used_count']);$tx->commit();return $msg;}catch(\Throwable $e){$tx->rollBack();throw $e;}
    }
}
