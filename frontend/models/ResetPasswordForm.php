<?php

declare(strict_types=1);
namespace frontend\models;
use yii\base\Model;
final class ResetPasswordForm extends Model { public string $password=''; public string $passwordRepeat=''; public function rules(): array{return [[['password','passwordRepeat'],'required'],['password','string','min'=>8,'max'=>72],['passwordRepeat','compare','compareAttribute'=>'password']];} }
