<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\{Vote, VoteService};
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

final class VotingController extends AdminController
{
    protected string $permission = 'manageShop';

    public function actionIndex(): string
    {
        return $this->render('index', [
            'serviceProvider' => new ActiveDataProvider([
                'query' => VoteService::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]),
                'pagination' => false,
            ]),
            'voteProvider' => new ActiveDataProvider([
                'query' => Vote::find()
                    ->with(['service', 'user'])
                    ->orderBy(['id' => SORT_DESC]),
                'pagination' => ['pageSize' => 50],
            ]),
        ]);
    }

    public function actionEdit(?int $id = null)
    {
        $model = $id !== null
            ? VoteService::findOne($id)
            : new VoteService([
                'driver' => VoteService::DRIVER_TOPG,
                'reward_amount' => 15.00,
                'reward_cooldown_seconds' => 86400,
                'enabled' => 1,
                'show_on_home' => 0,
                'sort' => 100,
            ]);

        if ($model === null) {
            throw new NotFoundHttpException();
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->code = strtolower(trim((string)$model->code));
            $model->name = trim((string)$model->name);
            $model->vote_url_template = trim((string)$model->vote_url_template);
            $model->callback_secret = trim((string)$model->callback_secret);
            $model->banner_url = trim((string)$model->banner_url);
            $model->config = trim((string)$model->config);

            if ($model->config !== '') {
                $decoded = json_decode($model->config, true);
                if (is_array($decoded)) {
                    $model->config = json_encode(
                        $decoded,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                    );
                }
            } else {
                $model->config = null;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Сервис голосования сохранён.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('form', ['model' => $model]);
    }
}
