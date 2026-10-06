<?php

declare(strict_types=1);namespace backend\controllers;use backend\components\AdminController;use common\models\NewsCategory;use Yii;use yii\data\ActiveDataProvider;use yii\web\NotFoundHttpException;
final class NewsCategoryController extends AdminController { protected string $permission='manageContent';public function actionIndex():string{return $this->render('index',['dataProvider'=>new ActiveDataProvider(['query'=>NewsCategory::find()->orderBy('name')])]);}public function actionEdit(?int $id=null){$m=$id?NewsCategory::findOne($id):new NewsCategory();if(!$m)throw new NotFoundHttpException();if($m->load(Yii::$app->request->post())&&$m->save())return $this->redirect(['index']);return $this->render('form',['model'=>$m]);}}
