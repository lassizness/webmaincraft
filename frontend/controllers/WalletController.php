<?php

declare(strict_types=1);namespace frontend\controllers;use common\models\{PaymentOrder,WalletTransaction};use common\services\WalletService;use frontend\components\Controller;use frontend\models\TopUpForm;use Yii;use yii\filters\AccessControl;
final class WalletController extends Controller
{
 public function behaviors():array{return ['access'=>['class'=>AccessControl::class,'rules'=>[['allow'=>true,'roles'=>['@']]]]];}
 public function actionIndex(){
 if(Yii::$app->request->isPost)
 {
  throw new \yii\web\ServiceUnavailableHttpException(
   'Пополнение баланса временно недоступно.'
  );
 }
 $m=new TopUpForm();if($m->load(Yii::$app->request->post())&&$m->validate()){$o=new PaymentOrder(['user_id'=>Yii::$app->user->id,'amount'=>$m->amount,'provider'=>'manual','status'=>PaymentOrder::STATUS_NEW,'external_id'=>bin2hex(random_bytes(12)),'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);$o->save(false);Yii::$app->session->setFlash('success','Заявка на пополнение создана. Пока платёжный провайдер не подключён, её подтверждает администратор.');return $this->refresh();}return $this->render('index',['wallet'=>(new WalletService())->wallet((int)Yii::$app->user->id),'transactions'=>WalletTransaction::find()->where(['user_id'=>Yii::$app->user->id])->orderBy(['id'=>SORT_DESC])->limit(50)->all(),'orders'=>PaymentOrder::find()->where(['user_id'=>Yii::$app->user->id])->orderBy(['id'=>SORT_DESC])->limit(20)->all(),'model'=>$m]);}
}
