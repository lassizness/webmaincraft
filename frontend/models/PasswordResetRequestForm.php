<?php

declare(strict_types=1);
namespace frontend\models;
use yii\base\Model;
final class PasswordResetRequestForm extends Model { public string $email=''; public function rules(): array{return [[['email'],'required'],['email','email'],['email','string','max'=>254]];} }
