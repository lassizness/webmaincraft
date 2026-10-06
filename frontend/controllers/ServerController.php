<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\models\Server;
use common\services\MinecraftStatusService;
use frontend\components\Controller;
use Yii;

final class ServerController extends Controller
{
    private const STATUS_CACHE_TTL = 30;

    public function actionIndex(): string
    {
        $rows = [];
        $svc = new MinecraftStatusService();

        foreach (
            Server::find()
                ->where(['enabled' => 1])
                ->orderBy(['sort' => SORT_ASC])
                ->all() as $server
        ) {
            $cacheKey = sprintf(
                'minecraft-status:%s:%d',
                $server->host,
                (int)$server->port
            );

            if (Yii::$app->has('cache')) {
                $status = Yii::$app->cache->getOrSet(
                    $cacheKey,
                    static fn() => $svc->status(
                        $server->host,
                        (int)$server->port,
                        5
                    ),
                    self::STATUS_CACHE_TTL
                );
            } else {
                $status = $svc->status(
                    $server->host,
                    (int)$server->port,
                    5
                );
            }

            $rows[] = [
                'server' => $server,
                'status' => $status,
            ];
        }

        return $this->render('index', [
            'rows' => $rows,
        ]);
    }
}
