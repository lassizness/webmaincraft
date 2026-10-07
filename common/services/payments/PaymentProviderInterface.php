<?php

declare(strict_types=1);

namespace common\services\payments;

use common\models\PaymentOrder;

interface PaymentProviderInterface
{
    public function code(): string;

    public function isConfigured(): bool;

    public function createPayment(PaymentOrder $order): PaymentInitResult;

    public function verifyPostback(array $payload): bool;
}
