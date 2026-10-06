<?php

declare(strict_types=1);
namespace common\services;
use common\models\WebSession;
use Yii;
final class SessionTracker
{
    public function register(int $userId): void
    {
        $sid=Yii::$app->session->id; if($sid===''){Yii::$app->session->open();$sid=Yii::$app->session->id;}
        $hash=hash('sha256',$sid);
        $m=WebSession::findOne(['token_hash'=>$hash]); if(!$m){$m=new WebSession();$m->token_hash=$hash;}
        $m->user_id=$userId; Yii::$app->session->set('ht_tracked_session',1); $m->ip_address=Yii::$app->request->userIP; $m->user_agent=mb_substr((string)Yii::$app->request->userAgent,0,512); $m->expires_at=date('Y-m-d H:i:s',time()+30*86400); $m->last_seen_at=date('Y-m-d H:i:s'); $m->revoked_at=null; $m->save(false);
    }
    public function touchAndValidate(int $userId): bool
    {
        if(!Yii::$app->session->get('ht_tracked_session')){ $this->register($userId); return true; }
        $hash=hash('sha256',Yii::$app->session->id); $m=WebSession::find()->where(['user_id'=>$userId,'token_hash'=>$hash,'revoked_at'=>null])->andWhere(['>','expires_at',date('Y-m-d H:i:s')])->one();
        if(!$m){return false;} if(strtotime($m->last_seen_at)<time()-300){$m->last_seen_at=date('Y-m-d H:i:s');$m->save(false,['last_seen_at']);} return true;
    }
    public function revokeCurrent(): void { if(!Yii::$app->user->isGuest){WebSession::updateAll(['revoked_at'=>date('Y-m-d H:i:s')],['user_id'=>Yii::$app->user->id,'token_hash'=>hash('sha256',Yii::$app->session->id)]);} }
    public function revoke(int $id,int $userId): bool { return WebSession::updateAll(['revoked_at'=>date('Y-m-d H:i:s')],['id'=>$id,'user_id'=>$userId])>0; }
    public function revokeAll(int $userId): void { WebSession::updateAll(['revoked_at'=>date('Y-m-d H:i:s')],['user_id'=>$userId,'revoked_at'=>null]); }
}
