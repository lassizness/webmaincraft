<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

final class PrivilegeNotification extends ActiveRecord
{
    public const TYPE_7D = '7d';
    public const TYPE_3D = '3d';
    public const TYPE_1D = '1d';
    public const TYPE_EXPIRED = 'expired';

    public static function tableName(): string
    {
        return '{{%privilege_notification_log}}';
    }
}
