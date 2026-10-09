<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\AchievementSeason;
use common\services\AuditService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

final class SeasonController extends AdminController
{
    protected string $permission = 'manageSeasons';

    public function actionIndex(): string
    {
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => AchievementSeason::find()
                    ->orderBy([
                        'sort' => SORT_ASC,
                        'id' => SORT_DESC,
                    ]),
                'pagination' => ['pageSize' => 30],
            ]),
        ]);
    }

    public function actionEdit(?int $id = null)
    {
        $model = $id
            ? AchievementSeason::findOne($id)
            : new AchievementSeason([
                'status' => AchievementSeason::STATUS_PLANNED,
                'sort' => 100,
            ]);

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        if(
            $model->load(Yii::$app->request->post())
            && $model->validate()
        )
        {
            $now = date('Y-m-d H:i:s');

            if($model->isNewRecord)
            {
                $model->created_at = $now;
            }

            $model->updated_at = $now;
            $model->save(false);

            AuditService::log(
                'admin.achievement_season.save',
                'achievement_season',
                (string)$model->id,
                ['code' => $model->code]
            );

            Yii::$app->session->setFlash(
                'success',
                'Сезон сохранён.'
            );

            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }
}
