<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class Page extends ActiveRecord
{
    public static function tableName(): string { return '{{%pages}}'; }
    public function rules(): array { return [[['title','slug','body'],'required'],[['body'],'string'],[['published','sort'],'integer'],[['title','slug','meta_title','meta_description'],'string','max'=>255],['slug','match','pattern'=>'/^[a-z0-9-]+$/'],['slug','unique']]; }
}
