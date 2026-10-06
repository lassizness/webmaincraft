<?php

declare(strict_types=1);
namespace frontend\models;
use common\models\User;use yii\base\Model;
final class ProfileForm extends Model { public string $email=''; public function rules():array{return [['email','required'],['email','email'],['email','string','max'=>254],['email','unique','targetClass'=>User::class,'targetAttribute'=>'email','filter'=>fn($q)=>$q->andWhere(['<>','id',\Yii::$app->user->id])]];} }
