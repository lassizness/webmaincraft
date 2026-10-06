<?php

declare(strict_types=1);namespace frontend\controllers;use common\models\{ShopCategory,ShopProduct,Setting};use common\services\{ShopService,PromoService};use frontend\components\Controller;use frontend\models\PromoForm;use Yii;use yii\filters\AccessControl;use yii\web\NotFoundHttpException;
final class ShopController extends Controller
{
 public function behaviors():array{return ['access'=>['class'=>AccessControl::class,'rules'=>[['allow'=>true,'actions'=>['index'],'roles'=>['?','@']],['allow'=>true,'actions'=>['buy','promo'],'roles'=>['@']]]]];}
 public function actionIndex():string{if((string)Setting::value('shop.enabled','1')!=='1')throw new \yii\web\ServiceUnavailableHttpException(Yii::t('ui','Магазин временно отключён.'));return $this->render('index',['categories'=>ShopCategory::find()->where(['enabled'=>1])->with(['products'=>fn($q)=>$q->andWhere(['enabled'=>1])->orderBy(['sort'=>SORT_ASC])])->orderBy(['sort'=>SORT_ASC])->all()]);}
 public function actionBuy(int $id){if(!Yii::$app->request->isPost)throw new \yii\web\MethodNotAllowedHttpException();$p=ShopProduct::findOne(['id'=>$id,'enabled'=>1]);if(!$p)throw new NotFoundHttpException();try{(new ShopService())->buy((int)Yii::$app->user->id,$p);Yii::$app->session->setFlash('success',Yii::t('ui','Покупка оплачена. Награда будет выдана автоматически.'));}catch(\Throwable $e){Yii::$app->session->setFlash('error',Yii::t('ui',$e->getMessage()));}return $this->redirect(['index']);}
 public function actionPromo(){ $m=new PromoForm();if($m->load(Yii::$app->request->post())&&$m->validate()){try{Yii::$app->session->setFlash('success',Yii::t('ui',(new PromoService())->redeem((int)Yii::$app->user->id,$m->code)));return $this->refresh();}catch(\Throwable $e){$m->addError('code',Yii::t('ui',$e->getMessage()));}}return $this->render('promo',['model'=>$m]);}
}
