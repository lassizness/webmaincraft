<?php

declare(strict_types=1);
namespace backend\components;
use yii\filters\AccessControl;
class AdminController extends \yii\web\Controller
{
    protected string $permission = 'adminPanel';
    public function behaviors(): array { return ['access'=>['class'=>AccessControl::class,'rules'=>[['allow'=>true,'roles'=>[$this->permission]]]]]; }
}
