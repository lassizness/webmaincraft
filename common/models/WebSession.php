<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class WebSession extends ActiveRecord
{
    public static function tableName(): string { return '{{%web_sessions}}'; }
    public function getUser(){ return $this->hasOne(User::class,['id'=>'user_id']); }
}
