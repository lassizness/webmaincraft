<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class UserToken extends ActiveRecord
{
    public const TYPE_EMAIL_VERIFY='email_verify';
    public const TYPE_PASSWORD_RESET='password_reset';
    public static function tableName(): string { return '{{%user_tokens}}'; }
    public function rules(): array { return [[['user_id','type','token_hash','expires_at'],'required'],['type','in','range'=>[self::TYPE_EMAIL_VERIFY,self::TYPE_PASSWORD_RESET]],['token_hash','string','length'=>64]]; }
    public function getUser(){ return $this->hasOne(User::class,['id'=>'user_id']); }
}
