<?php

declare(strict_types=1);

namespace common\services;

use common\models\SiteCounter;

final class SiteCounterService
{
    public static function render(string $position): string
    {
        $field = match($position) {
            'head' => 'head_code',
            'body_start' => 'body_start_code',
            'body_end' => 'body_end_code',

            default => throw new \InvalidArgumentException(
                'Unknown counter position: ' . $position
            ),
        };

        $counters = SiteCounter::find()
            ->where(['enabled' => 1])
            ->orderBy([
                'sort' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->asArray()
            ->all();

        $result = [];

        foreach($counters as $counter)
        {
            $code = trim((string)($counter[$field] ?? ''));

            if($code === '')
            {
                continue;
            }

            $result[] = $code;
        }

        if(!$result)
        {
            return '';
        }

        return "\n" . implode("\n", $result) . "\n";
    }

    /**
     * Оставлено для совместимости с CounterController.
     * Кэш счётчиков больше не используется.
     */
    public static function flushCache(): void
    {
    }
}
