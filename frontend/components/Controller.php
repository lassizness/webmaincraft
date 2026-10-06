<?php

declare(strict_types=1);
namespace frontend\components;
use common\services\SessionTracker;
use Yii;
class Controller extends \yii\web\Controller
{
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) return false;
        if (!Yii::$app->user->isGuest && !in_array($action->uniqueId, ['auth/logout'], true)) {
            if (!(new SessionTracker())->touchAndValidate((int)Yii::$app->user->id)) {
                Yii::$app->user->logout(false);
                Yii::$app->session->destroy();
                if (!Yii::$app->request->isAjax) {
                    Yii::$app->session->setFlash('warning', Yii::t('ui', 'Сессия завершена. Войдите снова.'));
                    Yii::$app->response->redirect(['/auth/login']);
                    return false;
                }
            }
        }
        return true;
    }
}
