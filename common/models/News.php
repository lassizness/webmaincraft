<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class News extends ActiveRecord
{
    public static function tableName(): string { return '{{%news}}'; }
    public function rules(): array { return [[['title','slug','body'],'required'],[['body','excerpt'],'string'],[['category_id','published','author_id'],'integer'],[['published_at'],'safe'],[['title','slug'],'string','max'=>255],['slug','match','pattern'=>'/^[a-z0-9-]+$/'],['slug','unique']]; }
    public function getCategory(){ return $this->hasOne(NewsCategory::class,['id'=>'category_id']); }
    public function getAuthor(){ return $this->hasOne(User::class,['id'=>'author_id']); }
}
