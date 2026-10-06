<?php

declare(strict_types=1);
namespace frontend\models;
use yii\base\Model;
final class PromoForm extends Model { public string $code=''; public function rules():array{return [['code','required'],['code','string','max'=>64]];} }
