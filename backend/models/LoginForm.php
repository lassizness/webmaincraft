<?php

declare(strict_types=1);namespace backend\models;use common\models\User;use yii\base\Model;
final class LoginForm extends Model { public string $login='';public string $password='';public bool $rememberMe=false;public function rules():array{return [[['login','password'],'required'],['rememberMe','boolean']];}public function login():bool{$u=User::findByLogin($this->login);if(!$u||$u->status!==User::STATUS_ACTIVE||!$u->validatePassword($this->password)){$this->addError('password','Неверные данные.');return false;}if(!\Yii::$app->authManager->checkAccess((int)$u->id,'adminPanel')){$this->addError('password','Нет доступа к админ-панели.');return false;}return \Yii::$app->user->login($u,$this->rememberMe?86400:0);}}
