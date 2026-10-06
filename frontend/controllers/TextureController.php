<?php

declare(strict_types=1);namespace frontend\controllers;use common\services\SkinService;use yii\web\Controller;use yii\web\NotFoundHttpException;
final class TextureController extends Controller
{
 public $enableCsrfValidation=false; public function actionSkin(string $username){return $this->send($username,'skin');}public function actionCloak(string $username){return $this->send($username,'cloak');}private function send(string $u,string $t){$p=(new SkinService())->pathByUsername($u,$t);if(!$p)throw new NotFoundHttpException();return \Yii::$app->response->sendFile($p,null,['mimeType'=>'image/png','inline'=>true]);}
}
