<?php

declare(strict_types=1);namespace backend\controllers;use backend\components\AdminController;use common\models\Server;use Yii;use yii\data\ActiveDataProvider;use yii\web\NotFoundHttpException;
final class ServerController extends AdminController { protected string $permission='manageServers'; public function actionIndex():string{return $this->render('index',['dataProvider'=>new ActiveDataProvider(['query'=>Server::find()->orderBy(['sort'=>SORT_ASC])])]);}public function actionEdit(?int $id=null){$m=$id?Server::findOne($id):new Server(['port'=>25565,'enabled'=>1,'sort'=>100,'rcon_env_key'=>'MAIN']);if(!$m)throw new NotFoundHttpException();if($m->load(Yii::$app->request->post())&&$m->save())return $this->redirect(['index']);return $this->render('form',['model'=>$m]);}}
