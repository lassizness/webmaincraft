<?php

declare(strict_types=1);

namespace common\services\payments;

final readonly class PaymentInitResult
{
    public function __construct(
        public string $externalId,
        public string $paymentUrl,
        public array $payload = []
    ) {
    }
}
