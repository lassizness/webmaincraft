<?php

declare(strict_types=1);

namespace common\services\voting;

final class VoteProviderResult
{
    public function __construct(
        public readonly string $playerName,
        public readonly ?string $voterIp,
        public readonly ?string $providerVoteId,
        public readonly string $callbackIp,
        public readonly array $payload,
    ) {
    }
}
