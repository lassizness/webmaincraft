<?php

declare(strict_types=1);

namespace console\controllers;

use common\models\DeliveryQueue;
use common\services\{
    DeliveryService,
    PrivilegeService
};
use yii\console\Controller;

final class DeliveryController extends Controller
{
    public function actionProcess(
        int $limit = 20
    ): int
    {
        $expired =
            (
                new PrivilegeService()
            )->expireDue(
                max(
                    20,
                    $limit
                )
            );

        if($expired > 0)
        {
            $this->stdout(
                "Expired privileges: {$expired}\n"
            );
        }

        $items =
            DeliveryQueue::find()
                ->where(
                    [
                        'status' =>
                            'pending',
                    ]
                )
                ->andWhere(
                    [
                        'or',
                        [
                            'available_at' =>
                                null,
                        ],
                        [
                            '<=',
                            'available_at',
                            date(
                                'Y-m-d H:i:s'
                            ),
                        ],
                    ]
                )
                ->orderBy(
                    [
                        'id' =>
                            SORT_ASC,
                    ]
                )
                ->limit(
                    $limit
                )
                ->all();

        $service =
            new DeliveryService();

        foreach($items as $queue)
        {
            $queue->status =
                'processing';

            $queue->attempts++;

            $queue->save(
                false,
                [
                    'status',
                    'attempts',
                ]
            );

            try
            {
                $service->process(
                    $queue
                );

                $queue->status =
                    'done';

                $queue->processed_at =
                    date(
                        'Y-m-d H:i:s'
                    );

                $queue->last_error =
                    null;
            }
            catch(\Throwable $e)
            {
                $queue->last_error =
                    mb_substr(
                        $e->getMessage(),
                        0,
                        65000
                    );

                $queue->status =
                    $queue->attempts >= 5
                        ? 'failed'
                        : 'pending';

                $queue->available_at =
                    date(
                        'Y-m-d H:i:s',
                        time()
                        + min(
                            3600,
                            60
                            * (
                                2
                                ** min(
                                    $queue->attempts,
                                    6
                                )
                            )
                        )
                    );
            }

            $queue->save(
                false
            );

            $this->stdout(
                "#{$queue->id}: {$queue->status}\n"
            );
        }

        return 0;
    }
}
