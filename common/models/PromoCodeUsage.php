<?php

declare(strict_types=1);
namespace common\models;
use yii\db\ActiveRecord;
final class PromoCodeUsage extends ActiveRecord { public static function tableName(): string { return '{{%promo_code_usages}}'; } }
