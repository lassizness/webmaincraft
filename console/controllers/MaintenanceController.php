<?php

declare(strict_types=1);namespace console\controllers;use common\models\{UserToken,WebSession,LoginAttempt};use yii\console\Controller;
final class MaintenanceController extends Controller
{
 public function actionCleanup():int{$now=date('Y-m-d H:i:s');$a=UserToken::deleteAll(['and',['<','expires_at',$now],['not',['used_at'=>null]]]);$b=WebSession::deleteAll(['and',['<','expires_at',$now],['not',['revoked_at'=>null]]]);$c=LoginAttempt::deleteAll(['<','created_at',date('Y-m-d H:i:s',time()-7*86400)]);$this->stdout("tokens=$a sessions=$b attempts=$c\n");return 0;}
}
