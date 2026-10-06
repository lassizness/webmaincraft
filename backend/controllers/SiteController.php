<?php

declare(strict_types=1);namespace backend\controllers;use backend\models\LoginForm;use Yii;use yii\web\Controller;use yii\filters\VerbFilter;
final class SiteController extends Controller { public function behaviors():array{return ['verbs'=>['class'=>VerbFilter::class,'actions'=>['logout'=>['post']]]];}public function actionLogin(){if(!Yii::$app->user->isGuest)return $this->redirect(['/dashboard/index']);$m=new LoginForm();if($m->load(Yii::$app->request->post())&&$m->validate()&&$m->login())return $this->redirect(['/dashboard/index']);return $this->render('login',['model'=>$m]);}public function actionLogout(){Yii::$app->user->logout();return $this->redirect(['login']);}public function actionError(){return $this->render('error',['exception'=>Yii::$app->errorHandler->exception]);}}
