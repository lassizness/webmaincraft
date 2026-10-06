<?php

declare(strict_types=1);
namespace console\controllers;
use common\models\User;use common\services\UuidService;use yii\console\Controller;use yii\console\ExitCode;
final class UserController extends Controller
{
 public function actionAssignAdmin(string $username):int{$u=User::findOne(['username'=>$username]);if(!$u){$this->stderr("User not found\n");return ExitCode::DATAERR;}$am=\Yii::$app->authManager;$role=$am->getRole('admin');if(!$role){$this->stderr("Run migrations first\n");return ExitCode::CONFIG;}foreach($am->getRolesByUser((int)$u->id) as $r)$am->revoke($r,(int)$u->id);$am->assign($role,(int)$u->id);$this->stdout("Admin assigned to {$u->username}\n");return ExitCode::OK;}
 public function actionCreateAdmin(string $username,string $email,string $password):int{if(User::find()->where(['or',['username'=>$username],['email'=>$email]])->exists()){$this->stderr("Username/email exists\n");return ExitCode::DATAERR;}$u=new User();$u->uuid=UuidService::v4();$u->username=$username;$u->email=mb_strtolower($email);$u->setPassword($password);$u->status=User::STATUS_ACTIVE;$u->email_verified=1;if(!$u->save()){$this->stderr(json_encode($u->errors,JSON_UNESCAPED_UNICODE)."\n");return ExitCode::DATAERR;}return $this->actionAssignAdmin($username);}
}
