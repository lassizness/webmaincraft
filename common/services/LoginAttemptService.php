<?php

declare(strict_types=1);
namespace common\services;
use common\models\LoginAttempt;
final class LoginAttemptService
{
    private const WINDOW=900; private const MAX=5;
    public function isBlocked(string $identifier,string $ip): bool { return LoginAttempt::find()->where(['identifier_hash'=>hash('sha256',mb_strtolower($identifier)),'ip_address'=>$ip,'success'=>0])->andWhere(['>','created_at',date('Y-m-d H:i:s',time()-self::WINDOW)])->count()>=self::MAX; }
    public function record(string $identifier,string $ip,bool $success): void { $m=new LoginAttempt(['identifier_hash'=>hash('sha256',mb_strtolower($identifier)),'ip_address'=>$ip,'success'=>$success?1:0,'created_at'=>date('Y-m-d H:i:s')]);$m->save(false); }
}
