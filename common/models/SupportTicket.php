<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class SupportTicket extends ActiveRecord
{
    public const STATUS_OPEN='open'; public const STATUS_ANSWERED='answered'; public const STATUS_CLOSED='closed';
    public static function tableName(): string { return '{{%support_tickets}}'; }
    public function rules(): array { return [[['user_id','subject'],'required'],[['user_id','assigned_to'],'integer'],[['subject'],'string','max'=>255],['status','in','range'=>[self::STATUS_OPEN,self::STATUS_ANSWERED,self::STATUS_CLOSED]]]; }
    public function getUser(){ return $this->hasOne(User::class,['id'=>'user_id']); }
    public function getMessages(){ return $this->hasMany(SupportMessage::class,['ticket_id'=>'id'])->orderBy(['id'=>SORT_ASC]); }
}
