<?php

declare(strict_types=1);
namespace common\services;
use common\models\UserToken;
use yii\db\Expression;
final class TokenService
{
    public function issue(int $userId,string $type,int $ttlSeconds): string
    {
        UserToken::deleteAll(['user_id'=>$userId,'type'=>$type,'used_at'=>null]);
        $raw=rtrim(strtr(base64_encode(random_bytes(32)),'+/','-_'),'=');
        $t=new UserToken(['user_id'=>$userId,'type'=>$type,'token_hash'=>hash('sha256',$raw),'expires_at'=>date('Y-m-d H:i:s',time()+$ttlSeconds)]);
        if(!$t->save()){ throw new \RuntimeException('Token save failed: '.json_encode($t->errors,JSON_UNESCAPED_UNICODE)); }
        return $raw;
    }
    public function consume(string $raw,string $type): ?UserToken
    {
        $t=UserToken::find()->where(['token_hash'=>hash('sha256',$raw),'type'=>$type,'used_at'=>null])->andWhere(['>','expires_at',date('Y-m-d H:i:s')])->one();
        if(!$t){return null;} $t->used_at=date('Y-m-d H:i:s'); $t->save(false,['used_at']); return $t;
    }
}
