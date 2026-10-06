<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class SupportAttachment extends ActiveRecord
{
    public static function tableName(): string { return '{{%support_attachments}}'; }
    public function rules(): array { return [[['message_id','stored_name','original_name','mime_type','size'],'required'],[['message_id','size'],'integer'],[['stored_name','original_name','mime_type'],'string','max'=>255]]; }
}
