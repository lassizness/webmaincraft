<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class DeliveryQueue extends ActiveRecord { public static function tableName(): string { return '{{%delivery_queue}}'; } }
