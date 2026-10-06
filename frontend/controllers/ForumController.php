<?php

declare(strict_types=1);namespace frontend\controllers;use common\services\DiscourseSsoService;use frontend\components\Controller;use Yii;use yii\filters\AccessControl;
final class ForumController extends Controller { public function behaviors():array{return ['access'=>['class'=>AccessControl::class,'rules'=>[['allow'=>true,'roles'=>['@']]]]];} public function actionSso(string $sso,string $sig){return $this->redirect((new DiscourseSsoService())->buildRedirect(Yii::$app->user->identity,$sso,$sig));} }
