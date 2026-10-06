<?php

declare(strict_types=1);namespace backend\controllers;use backend\components\AdminController;use common\models\AuditLog;use yii\data\ActiveDataProvider;
final class AuditController extends AdminController { protected string $permission='viewAudit'; public function actionIndex():string{return $this->render('index',['dataProvider'=>new ActiveDataProvider(['query'=>AuditLog::find()->orderBy(['id'=>SORT_DESC]),'pagination'=>['pageSize'=>50]])]);}}
