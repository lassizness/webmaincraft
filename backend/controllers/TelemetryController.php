<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\{
    AchievementUnlock,
    PlayerSession,
    TelemetryEvent
};
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;

final class TelemetryController extends AdminController
{
    protected string $permission = 'viewTelemetry';

    public function actionIndex(): string
    {
        $now = time();
        $fiveMinutesAgo = date('Y-m-d H:i:s', $now - 300);
        $today = date('Y-m-d 00:00:00');

        $serverRows = (new Query())
            ->from(['e' => '{{%telemetry_events}}'])
            ->leftJoin(['s' => '{{%servers}}'], 's.id = e.server_id')
            ->select([
                'server_id' => 'e.server_id',
                'server_name' => 's.name',
                'server_code' => 's.code',
                'last_event' => 'MAX(e.received_at)',
                'events_5m' => new \yii\db\Expression(
                    'SUM(CASE WHEN e.received_at >= :since THEN 1 ELSE 0 END)',
                    [':since' => $fiveMinutesAgo]
                ),
                'events_total' => 'COUNT(*)',
            ])
            ->groupBy(['e.server_id', 's.name', 's.code'])
            ->orderBy(['last_event' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'events5m' => TelemetryEvent::find()
                ->where(['>=', 'received_at', $fiveMinutesAgo])
                ->count(),
            'eventsToday' => TelemetryEvent::find()
                ->where(['>=', 'received_at', $today])
                ->count(),
            'lastEventAt' => TelemetryEvent::find()
                ->max('received_at'),
            'onlinePlayers' => PlayerSession::find()
                ->where(['ended_at' => null])
                ->andWhere(['>=', 'last_event_at', $fiveMinutesAgo])
                ->count(),
            'unlocksToday' => AchievementUnlock::find()
                ->where(['>=', 'unlocked_at', $today])
                ->count(),
            'serverRows' => $serverRows,
        ]);
    }

    public function actionEvents(): string
    {
        $query = TelemetryEvent::find()
            ->with(['user', 'server'])
            ->orderBy([
                'occurred_at' => SORT_DESC,
                'id' => SORT_DESC,
            ]);

        $type = trim((string)Yii::$app->request->get('type'));
        $source = trim((string)Yii::$app->request->get('source'));
        $q = trim((string)Yii::$app->request->get('q'));

        if($type !== '')
        {
            $query->andWhere(['event_type' => $type]);
        }

        if($source !== '')
        {
            $query->andWhere(['source' => $source]);
        }

        if($q !== '')
        {
            $query->andWhere([
                'or',
                ['like', 'username', $q],
                ['like', 'object_id', $q],
                ['player_uuid' => $q],
            ]);
        }

        return $this->render('events', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 50],
            ]),
            'type' => $type,
            'source' => $source,
            'q' => $q,
            'eventTypes' => TelemetryEvent::find()
                ->select('event_type')
                ->distinct()
                ->orderBy('event_type')
                ->column(),
            'sources' => TelemetryEvent::find()
                ->select('source')
                ->distinct()
                ->orderBy('source')
                ->column(),
        ]);
    }
}
