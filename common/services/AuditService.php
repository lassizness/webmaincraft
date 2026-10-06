<?php

declare(strict_types=1);
namespace common\services;
use common\models\AuditLog;
use Yii;
final class AuditService
{
    public static function log(string $action,string $entityType='',?string $entityId=null,array $data=[]): void
    {
        $m=new AuditLog();$m->user_id=Yii::$app->has('user')&&!Yii::$app->user->isGuest?Yii::$app->user->id:null;$m->action=$action;$m->entity_type=$entityType;$m->entity_id=$entityId;$m->ip_address=Yii::$app instanceof \yii\web\Application?Yii::$app->request->userIP:null;$m->data_json=$data?json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):null;$m->created_at=date('Y-m-d H:i:s');$m->save(false);
    }
}
