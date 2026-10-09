<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\{
    Achievement,
    AchievementCondition,
    AchievementReward,
    AchievementSeason
};
use common\services\AuditService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

final class AchievementController extends AdminController
{
    protected string $permission = 'manageAchievements';

    public function actionIndex(): string
    {
        $query = Achievement::find()
            ->with('season')
            ->orderBy([
                'sort' => SORT_ASC,
                'id' => SORT_ASC,
            ]);

        $category = trim((string)Yii::$app->request->get('category'));

        if($category !== '')
        {
            $query->andWhere(['category' => $category]);
        }

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 50],
            ]),
            'category' => $category,
        ]);
    }

    public function actionEdit(?int $id = null)
    {
        $model = $id
            ? Achievement::findOne($id)
            : new Achievement([
                'category' => 'general',
                'rarity' => 'common',
                'score' => 10,
                'hidden' => 0,
                'enabled' => 1,
                'sort' => 100,
            ]);

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        if(Yii::$app->request->isPost)
        {
            $model->hidden = 0;
            $model->enabled = 0;

            if($model->load(Yii::$app->request->post()))
            {
                $model->season_id = $model->season_id ?: null;
                $now = date('Y-m-d H:i:s');

                if($model->isNewRecord)
                {
                    $model->created_at = $now;
                }

                $model->updated_at = $now;

                if($model->validate())
                {
                    $transaction = Yii::$app->db->beginTransaction();

                    try
                    {
                        $model->save(false);

                        AchievementCondition::deleteAll([
                            'achievement_id' => $model->id,
                        ]);
                        AchievementReward::deleteAll([
                            'achievement_id' => $model->id,
                        ]);

                        $this->saveConditions(
                            (int)$model->id,
                            (array)Yii::$app->request->post(
                                'conditions',
                                []
                            )
                        );
                        $this->saveRewards(
                            (int)$model->id,
                            (array)Yii::$app->request->post(
                                'rewards',
                                []
                            )
                        );

                        $transaction->commit();

                        AuditService::log(
                            'admin.achievement.save',
                            'achievement',
                            (string)$model->id,
                            ['code' => $model->code]
                        );

                        Yii::$app->session->setFlash(
                            'success',
                            'Достижение сохранено.'
                        );

                        return $this->redirect([
                            'edit',
                            'id' => $model->id,
                        ]);
                    }
                    catch(\Throwable $e)
                    {
                        $transaction->rollBack();
                        throw $e;
                    }
                }
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'conditions' => $model->isNewRecord
                ? []
                : $model->conditions,
            'rewards' => $model->isNewRecord
                ? []
                : $model->rewards,
            'seasons' => AchievementSeason::find()
                ->orderBy(['sort' => SORT_ASC, 'id' => SORT_DESC])
                ->all(),
        ]);
    }

    public function actionToggle(int $id)
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }

        $model = Achievement::findOne($id);

        if(!$model)
        {
            throw new NotFoundHttpException();
        }

        $model->enabled = $model->enabled ? 0 : 1;
        $model->updated_at = date('Y-m-d H:i:s');
        $model->save(false, ['enabled', 'updated_at']);

        AuditService::log(
            'admin.achievement.toggle',
            'achievement',
            (string)$model->id,
            ['enabled' => (bool)$model->enabled]
        );

        return $this->redirect(['index']);
    }

    private function saveConditions(
        int $achievementId,
        array $rows
    ): void
    {
        $sort = 10;

        foreach($rows as $row)
        {
            $statKey = trim((string)($row['stat_key'] ?? ''));

            if($statKey === '')
            {
                continue;
            }

            $operator = (string)($row['operator'] ?? '>=');

            if(!in_array($operator, AchievementCondition::OPERATORS, true))
            {
                $operator = '>=';
            }

            $condition = new AchievementCondition([
                'achievement_id' => $achievementId,
                'group_no' => max(1, (int)($row['group_no'] ?? 1)),
                'stat_key' => mb_substr($statKey, 0, 191),
                'operator' => $operator,
                'target_value' => (int)($row['target_value'] ?? 0),
                'sort' => $sort,
            ]);

            if(!$condition->validate())
            {
                throw new \RuntimeException(
                    'Некорректное условие достижения.'
                );
            }

            $condition->save(false);
            $sort += 10;
        }
    }

    private function saveRewards(
        int $achievementId,
        array $rows
    ): void
    {
        $allowed = [
            'wallet',
            'rcon',
            'permission',
            'privilege',
        ];
        $sort = 10;

        foreach($rows as $row)
        {
            $type = trim((string)($row['reward_type'] ?? ''));

            if(!in_array($type, $allowed, true))
            {
                continue;
            }

            $payload = trim((string)($row['payload_json'] ?? ''));

            if($payload !== '')
            {
                json_decode($payload, true);

                if(json_last_error() !== JSON_ERROR_NONE)
                {
                    throw new \RuntimeException(
                        'Payload награды должен быть валидным JSON.'
                    );
                }
            }

            $reward = new AchievementReward([
                'achievement_id' => $achievementId,
                'reward_type' => $type,
                'payload_json' => $payload !== '' ? $payload : null,
                'sort' => $sort,
            ]);
            $reward->save(false);
            $sort += 10;
        }
    }
}
