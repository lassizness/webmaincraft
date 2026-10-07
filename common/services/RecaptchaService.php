<?php

declare(strict_types=1);

namespace common\services;

use common\models\Setting;
use Yii;

final class RecaptchaService
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public const SCOPE_LOGIN = 'login';
    public const SCOPE_REGISTER = 'register';

    public function isEnabled(): bool
    {
        return (string)Setting::value('recaptcha.enabled', '0') === '1';
    }

    public function isEnabledFor(string $scope): bool
    {
        if(!$this->isEnabled())
        {
            return false;
        }

        $key = match($scope)
        {
            self::SCOPE_LOGIN => 'recaptcha.login.enabled',
            self::SCOPE_REGISTER => 'recaptcha.register.enabled',
            default => null,
        };

        if($key === null)
        {
            return false;
        }

        return (string)Setting::value($key, '1') === '1';
    }

    public function isConfigured(): bool
    {
        return $this->getSiteKey() !== ''
            && $this->getSecretKey() !== '';
    }

    public function isMonitoringOnly(): bool
    {
        return (string)Setting::value(
            'recaptcha.monitoring_only',
            '1'
        ) === '1';
    }

    public function getSiteKey(): string
    {
        return trim((string)Setting::value(
            'recaptcha.site_key',
            ''
        ));
    }

    public function getThreshold(string $scope): float
    {
        $key = match($scope)
        {
            self::SCOPE_LOGIN => 'recaptcha.login.threshold',
            self::SCOPE_REGISTER => 'recaptcha.register.threshold',
            default => null,
        };

        if($key === null)
        {
            return 0.5;
        }

        $value = (float)Setting::value($key, '0.5');

        return max(0.0, min(1.0, $value));
    }

    public function verify(
        ?string $response,
        string $scope,
        ?string $remoteIp = null
    ): bool
    {
        $response = trim((string)$response);

        if(
            !$this->isConfigured()
            || $response === ''
            || !in_array(
                $scope,
                [
                    self::SCOPE_LOGIN,
                    self::SCOPE_REGISTER,
                ],
                true
            )
        )
        {
            return false;
        }

        if(!function_exists('curl_init'))
        {
            Yii::error(
                'PHP cURL extension is not available',
                'recaptcha'
            );
            return false;
        }

        $payload = [
            'secret' => $this->getSecretKey(),
            'response' => $response,
        ];

        $remoteIp = trim((string)$remoteIp);

        if($remoteIp !== '')
        {
            $payload['remoteip'] = $remoteIp;
        }

        $curl = curl_init(self::VERIFY_URL);

        if($curl === false)
        {
            Yii::error(
                'Unable to initialize cURL',
                'recaptcha'
            );
            return false;
        }

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(
                $payload,
                '',
                '&',
                PHP_QUERY_RFC3986
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
            CURLOPT_USERAGENT => 'HiveTech-Portal/1.0 reCAPTCHA',
        ]);

        $body = curl_exec($curl);

        if($body === false)
        {
            Yii::warning(
                'reCAPTCHA request failed: '.curl_error($curl),
                'recaptcha'
            );
            curl_close($curl);
            return false;
        }

        $httpCode = (int)curl_getinfo(
            $curl,
            CURLINFO_RESPONSE_CODE
        );

        curl_close($curl);

        if($httpCode < 200 || $httpCode >= 300)
        {
            Yii::warning(
                'reCAPTCHA verification returned HTTP '
                .$httpCode,
                'recaptcha'
            );
            return false;
        }

        $data = json_decode($body, true);

        if(!is_array($data))
        {
            Yii::warning(
                'Invalid reCAPTCHA response',
                'recaptcha'
            );
            return false;
        }

        $success = ($data['success'] ?? false) === true;
        $action = (string)($data['action'] ?? '');
        $score = isset($data['score'])
            ? (float)$data['score']
            : null;
        $threshold = $this->getThreshold($scope);

        if(!$success)
        {
            Yii::info([
                'event' => 'recaptcha.verify.failed',
                'scope' => $scope,
                'errorCodes' => $data['error-codes'] ?? [],
            ], 'recaptcha');
            return false;
        }

        if($action !== $scope)
        {
            Yii::warning([
                'event' => 'recaptcha.action_mismatch',
                'expectedAction' => $scope,
                'actualAction' => $action,
                'score' => $score,
                'hostname' => $data['hostname'] ?? null,
            ], 'recaptcha');
            return false;
        }

        if($score === null)
        {
            Yii::warning([
                'event' => 'recaptcha.score_missing',
                'scope' => $scope,
                'hostname' => $data['hostname'] ?? null,
            ], 'recaptcha');
            return false;
        }

        $passed = $score >= $threshold;

        Yii::info([
            'event' => 'recaptcha.score',
            'scope' => $scope,
            'score' => $score,
            'threshold' => $threshold,
            'passed' => $passed,
            'monitoringOnly' => $this->isMonitoringOnly(),
            'hostname' => $data['hostname'] ?? null,
        ], 'recaptcha');

        if(!$passed && !$this->isMonitoringOnly())
        {
            return false;
        }

        return true;
    }

    private function getSecretKey(): string
    {
        return trim((string)Setting::value(
            'recaptcha.secret_key',
            ''
        ));
    }
}
