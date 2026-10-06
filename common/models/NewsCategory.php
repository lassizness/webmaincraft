<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class NewsCategory extends ActiveRecord
{
    public static function tableName(): string { return '{{%news_categories}}'; }
    public function rules(): array { return [[['name','slug'],'required'],[['name'],'string','max'=>128],[['slug'],'match','pattern'=>'/^[a-z0-9-]+$/'],['slug','unique']]; }
    public function getNews(){ return $this->hasMany(News::class,['category_id'=>'id']); }
}
