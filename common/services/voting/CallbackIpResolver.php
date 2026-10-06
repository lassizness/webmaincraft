<?php

declare(strict_types=1);

namespace common\services\voting;

use yii\web\Request;

final class CallbackIpResolver
{
    public function resolve(Request $request): string
    {
        $remote = trim((string)($_SERVER['REMOTE_ADDR'] ?? $request->userIP ?? ''));
        if (!$this->isValidIp($remote)) {
            return '';
        }

        if (!$this->isTrustedProxy($remote)) {
            return $remote;
        }

        $forwarded = trim((string)$request->headers->get('X-Forwarded-For', ''));
        if ($forwarded === '') {
            return $remote;
        }

        $chain = [];
        foreach (explode(',', $forwarded) as $candidate) {
            $candidate = trim($candidate);
            if ($this->isValidIp($candidate)) {
                $chain[] = $candidate;
            }
        }
        $chain[] = $remote;

        // Walk from the application back toward the client and strip only proxies
        // explicitly trusted by us. This prevents spoofing the left-most XFF value.
        for ($i = count($chain) - 1; $i >= 0; --$i) {
            if (!$this->isTrustedProxy($chain[$i])) {
                return $chain[$i];
            }
        }

        return $remote;
    }

    private function isTrustedProxy(string $ip): bool
    {
        $configured = (string)\ht_env('VOTE_TRUSTED_PROXY_IPS', '10.254.254.1');
        $trusted = array_filter(array_map('trim', explode(',', $configured)));
        return in_array($ip, $trusted, true);
    }

    private function isValidIp(string $ip): bool
    {
        return $ip !== '' && filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
}
