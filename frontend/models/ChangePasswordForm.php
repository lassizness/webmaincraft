<?php

declare(strict_types=1);
namespace frontend\models;
use yii\base\Model;
final class ChangePasswordForm extends Model { public string $currentPassword='';public string $password='';public string $passwordRepeat='';public function rules():array{return [[['currentPassword','password','passwordRepeat'],'required'],['password','string','min'=>8,'max'=>72],['passwordRepeat','compare','compareAttribute'=>'password']];} }
