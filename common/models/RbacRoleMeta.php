<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class RbacRoleMeta extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%rbac_role_meta}}';
    }

    public function rules(): array
    {
        return [
            [['role_name', 'label', 'level'], 'required'],
            [['level', 'is_system'], 'integer'],
            [['role_name'], 'string', 'max' => 64],
            [['label'], 'string', 'max' => 128],
            [['role_name'], 'unique'],
            ['level', 'integer', 'min' => 1, 'max' => 100],
        ];
    }
}
