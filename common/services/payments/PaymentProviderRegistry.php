<?php

declare(strict_types=1);

namespace common\services\payments;

final class PaymentProviderRegistry
{
    public function get(string $code): PaymentProviderInterface
    {
        return match($code) {
            'paypalych' => new PayPalychProvider(),
            default => throw new \InvalidArgumentException('Unknown payment provider: ' . $code),
        };
    }
}
