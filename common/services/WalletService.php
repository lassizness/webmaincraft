<?php

declare(strict_types=1);
namespace common\services;
use common\models\{Wallet,WalletTransaction};use Yii;
final class WalletService
{
    public function wallet(int $userId): Wallet { $w=Wallet::findOne(['user_id'=>$userId]); if(!$w){$w=new Wallet(['user_id'=>$userId,'balance'=>0]);$w->save(false);} return $w; }
    public function credit(int $userId,float $amount,string $type,string $description,?string $refType=null,?int $refId=null): void { if($amount<=0)throw new \InvalidArgumentException();$this->change($userId,$amount,$type,$description,$refType,$refId); }
    public function debit(int $userId,float $amount,string $type,string $description,?string $refType=null,?int $refId=null): void { if($amount<=0)throw new \InvalidArgumentException();$this->change($userId,-$amount,$type,$description,$refType,$refId); }
    private function change(int $userId,float $delta,string $type,string $description,?string $refType,?int $refId): void
    {
        $db=Yii::$app->db;$own=$db->getTransaction()===null;$tx=$own?$db->beginTransaction():null;
        try{
            $row=$db->createCommand('SELECT id,balance FROM {{%wallets}} WHERE user_id=:uid FOR UPDATE',[':uid'=>$userId])->queryOne();
            if(!$row){$db->createCommand()->insert('{{%wallets}}',['user_id'=>$userId,'balance'=>0])->execute();$row=$db->createCommand('SELECT id,balance FROM {{%wallets}} WHERE user_id=:uid FOR UPDATE',[':uid'=>$userId])->queryOne();}
            $new=round((float)$row['balance']+$delta,2);if($new<0)throw new \DomainException('Недостаточно средств');
            $db->createCommand()->update('{{%wallets}}',['balance'=>$new],['id'=>$row['id']])->execute();
            $t=new WalletTransaction(['user_id'=>$userId,'amount'=>$delta,'balance_after'=>$new,'type'=>$type,'description'=>$description,'reference_type'=>$refType,'reference_id'=>$refId,'created_at'=>date('Y-m-d H:i:s')]);if(!$t->save())throw new \RuntimeException('Wallet transaction failed');
            if($own)$tx?->commit();
        }catch(\Throwable $e){if($own&&$tx?->isActive)$tx->rollBack();throw $e;}
    }
}
