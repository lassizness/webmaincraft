<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class Server extends ActiveRecord
{
    public static function tableName(): string { return '{{%servers}}'; }
    public function rules(): array { return [[['name','code','host','port'],'required'],[['port','enabled','sort'],'integer'],[['name','code','host','rcon_env_key','description'],'string','max'=>255],['code','unique']]; }
}
