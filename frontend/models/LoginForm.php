<?php

declare(strict_types=1);
namespace frontend\models;
use common\models\User;use yii\base\Model;
final class LoginForm extends Model
{
    public string $login=''; public string $password=''; public bool $rememberMe=true; private ?User $_user=null;
    public function rules(): array{return [[['login','password'],'required'],['rememberMe','boolean'],['password','string','min'=>1,'max'=>72],['login','string','max'=>254]];}
    public function getUser(): ?User { if($this->_user===null)$this->_user=User::findByLogin(trim($this->login)); return $this->_user; }
}
