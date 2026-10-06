<?php

declare(strict_types=1);namespace common\models;use yii\db\ActiveRecord;
final class UserPermission extends ActiveRecord { public static function tableName():string{return '{{%user_permissions}}';}public function rules():array{return [[['uuid','name'],'required'],[['uuid'],'string','max'=>36],[['name'],'string','max'=>100],[['uuid','name'],'unique','targetAttribute'=>['uuid','name']]];} }
