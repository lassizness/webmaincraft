<?php

declare(strict_types=1);
namespace frontend\models;
use yii\base\Model;
final class TopUpForm extends Model { public float $amount=100; public function rules():array{return [['amount','required'],['amount','number','min'=>10,'max'=>100000]];} }
