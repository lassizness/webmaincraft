<?php

declare(strict_types=1);
namespace frontend\models;
use yii\base\Model;
final class ReplyForm extends Model { public string $message=''; public function rules():array{return [[['message'],'required'],['message','string','min'=>2]];} }
