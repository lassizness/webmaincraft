<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class SupportMessage extends ActiveRecord
{
    public static function tableName(): string { return '{{%support_messages}}'; }
    public function rules(): array { return [[['ticket_id','user_id','body'],'required'],[['ticket_id','user_id','is_staff'],'integer'],[['body'],'string']]; }
    public function getAuthor(){ return $this->hasOne(User::class,['id'=>'user_id']); }
    public function getAttachments(){ return $this->hasMany(SupportAttachment::class,['message_id'=>'id']); }
}
