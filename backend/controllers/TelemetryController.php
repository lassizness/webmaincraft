<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\{
    AchievementUnlock,
    PlayerSession,
    ServerTelemetryStatus,
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
            ->from(['s' => '{{%servers}}'])
            ->leftJoin(
                ['t' => '{{%server_telemetry_status}}'],
                't.server_id = s.id'
            )
            ->where(['s.enabled' => 1])
            ->select([
                'server_id' => 's.id',
                'server_name' => 's.name',
                'server_code' => 's.code',
                'telemetry_version' => 't.telemetry_version',
                'minecraft_version' => 't.minecraft_version',
                'forge_version' => 't.forge_version',
                'online_players' => 'COALESCE(t.online_players, 0)',
                'queue_size' => 'COALESCE(t.queue_size, 0)',
                'buffered_events' => 'COALESCE(t.buffered_events, 0)',
                'events_received_total' => 'COALESCE(t.events_received_total, 0)',
                'last_batch_size' => 'COALESCE(t.last_batch_size, 0)',
                'last_batch_at' => 't.last_batch_at',
                'last_event_at' => 't.last_event_at',
                'last_heartbeat_at' => 't.last_heartbeat_at',
                'last_seen_ip' => 't.last_seen_ip',
                'last_error' => 't.last_error',
            ])
            ->orderBy([
                's.sort' => SORT_ASC,
                's.id' => SORT_ASC,
            ])
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
