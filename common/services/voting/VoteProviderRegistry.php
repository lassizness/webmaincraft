<?php

declare(strict_types=1);

namespace common\services\voting;

final class VoteProviderRegistry
{
    public function get(string $driver): VoteProviderInterface
    {
        return match ($driver) {
            'topg' => new TopGVoteProvider(),
            'topcraft' => new TopCraftVoteProvider(),
            default => throw new \DomainException('Unsupported voting provider: ' . $driver),
        };
    }
}
