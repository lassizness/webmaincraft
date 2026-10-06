<?php

declare(strict_types=1);
namespace common\services;
use common\models\User;
use common\models\UserToken;
use common\models\Wallet;
use Yii;
final class RegistrationService
{
    public function register(string $username,string $email,string $password): User
    {
        $tx=Yii::$app->db->beginTransaction();
        try{
            $u=new User(); $u->uuid=UuidService::v4(); $u->username=$username; $u->email=mb_strtolower($email); $u->setPassword($password); $u->status=User::STATUS_PENDING; $u->email_verified=0;
            if(!$u->save()){throw new \DomainException(json_encode($u->errors,JSON_UNESCAPED_UNICODE));}
            $w=new Wallet(['user_id'=>$u->id,'balance'=>0]); if(!$w->save()){throw new \RuntimeException('Wallet create failed');}
            $role=Yii::$app->authManager->getRole('user'); if($role){Yii::$app->authManager->assign($role,(int)$u->id);}
            $raw=(new TokenService())->issue((int)$u->id,UserToken::TYPE_EMAIL_VERIFY,86400);
            $tx->commit();
            $url=Yii::$app->params['appUrl'].'/verify-email?token='.rawurlencode($raw);
            try { Yii::$app->mailer->compose('verify-email',['user'=>$u,'url'=>$url])->setTo($u->email)->setSubject('Подтверждение регистрации HiveTech')->send(); } catch (\Throwable $mailError) { Yii::error($mailError,'mail'); }
            return $u;
        }catch(\Throwable $e){$tx->rollBack();throw $e;}
    }
    public function verify(string $token): ?User
    {
        $t=(new TokenService())->consume($token,UserToken::TYPE_EMAIL_VERIFY); if(!$t){return null;}
        $u=$t->user; if(!$u){return null;} $u->email_verified=1; if($u->status===User::STATUS_PENDING){$u->status=User::STATUS_ACTIVE;} $u->save(false,['email_verified','status']); return $u;
    }
}
