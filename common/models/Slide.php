<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class Slide extends ActiveRecord
{
    public static function tableName(): string { return '{{%slides}}'; }
    public function rules(): array { return [[['title'],'required'],[['subtitle'],'string'],[['enabled','sort'],'integer'],[['title','image_url','button_text','button_url'],'string','max'=>255]]; }
}
