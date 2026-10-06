<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class LoginAttempt extends ActiveRecord
{
    public static function tableName(): string { return '{{%login_attempts}}'; }
}
