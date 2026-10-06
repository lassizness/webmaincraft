<?php

declare(strict_types=1);
namespace frontend\models;
use common\models\User;use Yii;use yii\base\Model;
final class SignupForm extends Model
{
    public string $username=''; public string $email=''; public string $password=''; public string $passwordRepeat=''; public bool $agree=false; public ?string $turnstileToken=null;
    public function rules(): array{return [[['username','email','password','passwordRepeat'],'required'],['username','match','pattern'=>'/^[A-Za-z0-9_]{3,16}$/','message'=>Yii::t('app','Логин: 3–16 символов, латиница, цифры и _')],['username','unique','targetClass'=>User::class,'targetAttribute'=>'username'],['email','email'],['email','string','max'=>254],['email','unique','targetClass'=>User::class,'targetAttribute'=>'email'],['password','string','min'=>8,'max'=>72],['passwordRepeat','compare','compareAttribute'=>'password'],['agree','compare','compareValue'=>true,'message'=>Yii::t('app','Нужно принять правила проекта')]];}
}
